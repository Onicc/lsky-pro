<?php

namespace App\Services;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Green\Green;
use App\Enums\ConfigKey;
use App\Enums\GroupConfigKey;
use App\Enums\ImagePermission;
use App\Enums\Scan\AliyunOption;
use App\Enums\Scan\NsfwJsOption;
use App\Enums\Scan\TencentOption;
use App\Enums\Strategy\CosOption;
use App\Enums\Strategy\FtpOption;
use App\Enums\Strategy\KodoOption;
use App\Enums\Strategy\LocalOption;
use App\Enums\Strategy\MinioOption;
use App\Enums\Strategy\OssOption;
use App\Enums\Strategy\S3Option;
use App\Enums\Strategy\SftpOption;
use App\Enums\Strategy\UssOption;
use App\Enums\Strategy\WebDavOption;
use App\Enums\StrategyKey;
use App\Enums\UserConfigKey;
use App\Enums\UserStatus;
use App\Exceptions\UploadException;
use App\Models\Group;
use App\Models\Image;
use App\Models\MediaWrite;
use App\Models\Strategy;
use App\Models\User;
use App\Utils;
use Aws\S3\S3Client;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Overtrue\Flysystem\Cos\CosAdapter;
use Overtrue\Flysystem\Qiniu\QiniuAdapter;
use Sabre\DAV\Client;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Ims\V20201229\ImsClient;
use TencentCloud\Ims\V20201229\Models\ImageModerationRequest;
use WispX\Flysystem\Upyun\UpyunAdapter;
use Zing\Flysystem\Oss\OssAdapter;
use OSS\OssClient;

class ImageService
{
    public function __construct(private readonly ImageProcessor $processor = new ImageProcessor())
    {
    }

    public function store(Request $request): Image
    {
        $file = $request->file('file');
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw new UploadException('请选择一个有效的图片文件');
        }

        $user = $request->user();
        if (! $user && ! Utils::config(ConfigKey::IsAllowGuestUpload, false)) {
            throw new UploadException('管理员关闭了游客上传');
        }

        $group = $user ? $user->group : Group::query()->where('is_guest', true)->first();
        if (! $group) {
            throw new UploadException('没有可用的上传角色组，请联系管理员');
        }

        $configs = $group->configs;
        $extension = strtolower($file->getClientOriginalExtension());
        $sourcePath = $file->getPathname();
        $maximumBytes = min((int) config('image.max_upload_bytes'), (int) ($configs->get(GroupConfigKey::MaximumFileSize) * 1024));

        if (! in_array($extension, ImageProcessor::SUPPORTED_EXTENSIONS, true)
            || ! in_array($extension, (array) $configs->get(GroupConfigKey::AcceptedFileSuffixes), true)) {
            throw new UploadException('不支持的文件类型');
        }

        if ($file->getSize() > $maximumBytes) {
            throw new UploadException('图片大小超出限制');
        }

        if ($user) {
            if (Utils::config(ConfigKey::IsUserNeedVerify) && ! $user->email_verified_at) {
                throw new UploadException('账户未验证');
            }
            if ($user->status !== UserStatus::Normal) {
                throw new UploadException('账号状态异常');
            }
        }

        $strategy = $this->selectStrategy($request, $group, $user);
        $permission = $this->selectPermission($request, $user);
        $expiresAt = $this->selectExpiration($request);
        if ($permission === ImagePermission::Private && ! $strategy->supportsPrivateMedia()) {
            throw new UploadException('该储存策略未配置私有访问，请选择支持私有访问的储存');
        }

        $albumId = $request->input('album_id', $user?->configs->get(UserConfigKey::DefaultAlbum));
        if ($albumId && (! $user || ! is_scalar($albumId) || ! ctype_digit((string) $albumId) || ! $user->albums()->whereKey($albumId)->exists())) {
            if ($request->has('album_id')) {
                throw new UploadException('相册不存在或无权访问');
            }
            $albumId = null;
        }

        $image = new Image();
        $image->key = bin2hex(random_bytes(16));
        $image->group_id = $group->id;
        $image->strategy_id = $strategy->id;
        $image->user_id = $user?->id;
        $image->album_id = $albumId ?: null;
        $image->permission = $permission;
        $image->expires_at = $expiresAt;
        $image->object_managed = true;
        $image->thumbnail_pending_at = now();
        $image->setRelation('strategy', $strategy);

        $requestKey = $request->header('Idempotency-Key');
        if ($requestKey !== null) {
            if (! preg_match('/\A[a-zA-Z0-9_-]{16,128}\z/', $requestKey)) {
                throw new UploadException('上传请求标识格式不正确');
            }
            $owner = $user ? 'user:'.$user->id : 'ip:'.$request->ip();
            $image->idempotency_key = hash('sha256', $owner.':'.$requestKey);
            $image->request_fingerprint = hash('sha256', json_encode([
                hash_file('sha256', $sourcePath),
                $file->getClientOriginalName(),
                $strategy->id,
                $image->album_id,
                $permission,
                $expiresAt?->toIso8601String(),
            ], JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR));

            if ($previous = $this->previousUpload($image)) {
                return $previous;
            }
            $this->checkPendingRequest($image);
        }

        $this->rateLimiter($configs, $request);
        $temporaryPath = null;

        try {
            [$extension, $temporaryPath] = $this->processor->prepare($sourcePath, $extension, $configs);
            $path = $temporaryPath ?: $sourcePath;
            clearstatcache(true, $path);
            $size = filesize($path);
            if ($size === false || $size < 1) {
                throw new UploadException('无法读取处理后的图片，请稍后重试');
            }
            if ($size > config('image.max_upload_bytes')) {
                throw new UploadException('处理后的图片大小超出限制');
            }

            [$width, $height, $mimetype] = $this->processor->inspect($path, $extension);
            $name = $this->objectName($configs, $file, $image->key, $extension, $user?->id);
            $directory = dirname($name);

            $image->fill([
                'md5' => hash_file('md5', $path),
                'sha1' => hash_file('sha1', $path),
                'sha256' => hash_file('sha256', $path),
                'path' => $directory === '.' ? '' : $directory,
                'name' => basename($name),
                'origin_name' => mb_substr(mb_convert_encoding($file->getClientOriginalName(), 'UTF-8', 'UTF-8'), 0, 255),
                'size' => ceil($size * 100 / 1024) / 100,
                'mimetype' => $mimetype,
                'extension' => $extension,
                'width' => $width,
                'height' => $height,
                'is_unhealthy' => false,
                'uploaded_ip' => $request->ip(),
            ]);

            if ($configs->get(GroupConfigKey::IsEnableScan)) {
                $settings = (array) $configs->get(GroupConfigKey::ScanConfigs, []);
                $driver = (string) ($settings['driver'] ?? '');
                $image->is_unhealthy = $this->scan($driver, collect($settings['drivers'][$driver] ?? []), $image, $path);
                if ($image->is_unhealthy && $configs->get(GroupConfigKey::ScannedAction) === 'delete') {
                    throw new UploadException('图片涉嫌违规，禁止上传');
                }
            }

            return $this->persist($image, $strategy, $path);
        } finally {
            if ($temporaryPath) {
                @unlink($temporaryPath);
            }
        }
    }

    private function selectStrategy(Request $request, Group $group, ?User $user): Strategy
    {
        $strategies = $group->strategies();

        if ($request->filled('strategy_id')) {
            $id = $request->input('strategy_id');
            if (! is_scalar($id) || ! ctype_digit((string) $id) || ! $strategy = $strategies->find($id)) {
                throw new UploadException('选定的策略不存在');
            }

            return $strategy;
        }

        $default = $user?->configs->get(UserConfigKey::DefaultStrategy);
        if ($default && $strategy = $group->strategies()->find($default)) {
            return $strategy;
        }

        return $strategies->orderBy('strategies.id')->first()
            ?? throw new UploadException('没有可用的储存，请联系管理员');
    }

    private function selectPermission(Request $request, ?User $user): int
    {
        if (! $user) {
            return ImagePermission::LinkOnly;
        }

        $permission = $request->input('permission', $user->configs->get(UserConfigKey::DefaultPermission, ImagePermission::LinkOnly));

        if (! is_scalar($permission) || ! in_array((string) $permission, ImagePermission::VALUES, true)) {
            throw new UploadException('图片访问权限无效');
        }

        return (int) $permission;
    }

    private function selectExpiration(Request $request): ?Carbon
    {
        $days = $request->input('retention_days');
        if ($days === null || $days === '' || (string) $days === '0') {
            return null;
        }

        if (! is_scalar($days) || ! in_array((int) $days, [7, 30], true)) {
            throw new UploadException('图片保留时间无效');
        }

        return now()->addDays((int) $days);
    }

    private function previousUpload(Image $image): ?Image
    {
        if (! $image->idempotency_key) {
            return null;
        }

        $previous = Image::query()->where('idempotency_key', $image->idempotency_key)->first();
        if ($previous && $previous->request_fingerprint !== $image->request_fingerprint) {
            throw new UploadException('此上传标识已经用于另一张图片');
        }

        return $previous;
    }

    private function persist(Image $image, Strategy $strategy, string $path): Image
    {
        $reservation = null;

        try {
            $reservation = $this->reserveWrite($image, $strategy);
            if ($reservation instanceof Image) {
                return $reservation;
            }

            $filesystem = $image->filesystem();
            $storageKey = $image->storagePath();
            if ($filesystem->fileExists($storageKey)) {
                throw new UploadException('无法保留图片的储存位置，请重试');
            }

            $handle = fopen($path, 'rb');
            if ($handle === false) {
                throw new UploadException('无法打开待上传图片');
            }

            try {
                // The durable journal precedes the write so a stopped worker can be recovered.
                $reserved = MediaWrite::query()->whereKey($reservation->id)->where('expires_at', '>', now())
                    ->update(['write_attempted' => true]);
                if (! $reserved) {
                    throw new UploadException('上传请求已超时，请重试');
                }

                $filesystem->writeStream($storageKey, $handle, ['mimetype' => $image->mimetype, 'visibility' => 'private']);
            } finally {
                if (is_resource($handle)) {
                    fclose($handle);
                }
            }

            return $this->finishWrite($image, $reservation);
        } catch (\Throwable $exception) {
            if ($reservation instanceof MediaWrite) {
                try {
                    DB::transaction(function () use ($reservation) {
                        MediaWrite::query()->whereKey($reservation->id)->lockForUpdate()->first()?->retire();
                    }, 3);
                } catch (\Throwable $cleanupException) {
                    Utils::e($cleanupException, '无法立即安排图片清理');
                }
            }

            if ($previous = $this->previousUpload($image)) {
                return $previous;
            }
            $this->checkPendingRequest($image);

            if ($exception instanceof UploadException) {
                throw $exception;
            }

            Utils::e($exception, '保存图片失败');
            throw new UploadException('图片保存失败，请稍后重试', previous: $exception);
        }
    }

    private function reserveWrite(Image $image, Strategy $strategy): Image|MediaWrite
    {
        return DB::transaction(function () use ($image, $strategy) {
            $owner = $this->lockOwner($image);

            if ($previous = $this->previousUpload($image)) {
                return $previous;
            }
            $this->checkPendingRequest($image);

            if ($owner) {
                $pendingSize = MediaWrite::query()->where('user_id', $owner->id)->where('expires_at', '>', now())->sum('size');
                if ($image->size + $pendingSize + $owner->images()->sum('size') > $owner->capacity) {
                    throw new UploadException('储存空间不足');
                }
            }

            $current = Strategy::query()->whereKey($strategy->id)->sharedLock()->firstOrFail();
            if ($current->key !== $strategy->key || $current->configs->all() !== $strategy->configs->all()) {
                throw new UploadException('储存策略刚刚变更，请重试');
            }

            return MediaWrite::query()->create([
                'user_id' => $image->user_id,
                'strategy_id' => $image->strategy_id,
                'image_key' => $image->key,
                'path' => $image->path,
                'name' => $image->name,
                'size' => $image->size,
                'idempotency_key' => $image->idempotency_key,
                'request_fingerprint' => $image->request_fingerprint,
                'expires_at' => now()->addHour(),
            ]);
        }, 3);
    }

    private function finishWrite(Image $image, MediaWrite $reservation): Image
    {
        return DB::transaction(function () use ($image, $reservation) {
            $owner = $this->lockOwner($image);
            $pending = MediaWrite::query()->whereKey($reservation->id)->lockForUpdate()->first();
            if (! $pending || $pending->expires_at->isPast()) {
                throw new UploadException('上传请求已超时，请重试');
            }

            $image->saveOrFail();
            $owner?->increment('image_num');
            $image->album?->increment('image_num');
            $pending->delete();

            return $image;
        });
    }

    private function lockOwner(Image $image): ?User
    {
        if (! $image->user_id) {
            return null;
        }

        $owner = User::query()->whereKey($image->user_id)->lockForUpdate()->first();
        if (! $owner || $owner->status !== UserStatus::Normal) {
            throw new UploadException('账号状态异常');
        }
        if (Utils::config(ConfigKey::IsUserNeedVerify) && ! $owner->email_verified_at) {
            throw new UploadException('账户未验证');
        }

        return $owner;
    }

    private function checkPendingRequest(Image $image): void
    {
        if (! $image->idempotency_key) {
            return;
        }

        $writing = MediaWrite::query()->where('idempotency_key', $image->idempotency_key)->first();
        if ($writing) {
            throw new UploadException($writing->request_fingerprint === $image->request_fingerprint
                ? '该图片的上传请求仍在处理，请稍后重试'
                : '此上传标识已经用于另一张图片');
        }
    }

    public function getAdapter(Strategy $strategy, bool $managed = false): FilesystemAdapter
    {
        $configs = $strategy->configs;
        return match ($strategy->key) {
            StrategyKey::Local => new LocalFilesystemAdapter(
                location: $this->localRoot($configs->get(LocalOption::Root), $managed)
            ),
            StrategyKey::S3 => new AwsS3V3Adapter(
                client: new S3Client([
                    'credentials' => [
                        'key'    => $configs->get(S3Option::AccessKeyId),
                        'secret' => $configs->get(S3Option::SecretAccessKey)
                    ],
                    'endpoint' => $configs->get(S3Option::Endpoint) ?: null,
                    'region' => $configs->get(S3Option::Region) ?: 'us-east-1',
                    'use_path_style_endpoint' => (bool) $configs->get('use_path_style_endpoint', false),
                    'version' => '2006-03-01',
                    'http' => ['connect_timeout' => 5, 'timeout' => 45],
                    'retries' => 2,
                ]),
                bucket: $configs->get(S3Option::Bucket),
            ),
            StrategyKey::Oss => new OssAdapter(
                client: new OssClient($configs->get(
                    OssOption::AccessKeyId),
                    $configs->get(OssOption::AccessKeySecret),
                    $configs->get(OssOption::Endpoint),
                ),
                bucket: $configs->get(OssOption::Bucket),
            ),
            StrategyKey::Cos => new CosAdapter($configs->only([
                CosOption::AppId, CosOption::SecretId, CosOption::SecretKey, CosOption::Region, CosOption::Bucket,
            ])->toArray()),
            StrategyKey::Kodo => new QiniuAdapter(
                accessKey: $configs->get(KodoOption::AccessKey),
                secretKey: $configs->get(KodoOption::SecretKey),
                bucket: $configs->get(KodoOption::Bucket),
                domain: $configs->get(KodoOption::Url),
            ),
            StrategyKey::Uss => new UpyunAdapter(
                service: $configs->get(UssOption::Service),
                operator: $configs->get(UssOption::Operator),
                password: $configs->get(UssOption::Password),
                domain: $configs->get(UssOption::Url),
            ),
            StrategyKey::Sftp => new SftpAdapter(
                connectionProvider: new SftpConnectionProvider(
                    host: $configs->get(SftpOption::Host),
                    username: $configs->get(SftpOption::Username),
                    password: $configs->get(SftpOption::Password),
                    privateKey: $configs->get(SftpOption::PrivateKey),
                    passphrase: (string)$configs->get(SftpOption::Passphrase),
                    port: (int) ($configs->get(SftpOption::Port) ?: 22),
                    useAgent: (bool) $configs->get(SftpOption::UseAgent),
                    hostFingerprint: $configs->get('host_fingerprint') ?: null
                ),
                root: $configs->get(SftpOption::Root),
            ),
            StrategyKey::Ftp => new FtpAdapter(
                connectionOptions: FtpConnectionOptions::fromArray([
                    'host' => $configs->get(FtpOption::Host),
                    'root' => $configs->get(FtpOption::Root),
                    'port' => (int)$configs->get(FtpOption::Port),
                    'ssl' => (bool)$configs->get(FtpOption::Ssl),
                    'username' => $configs->get(FtpOption::Username),
                    'password' => $configs->get(FtpOption::Password),
                    'passive' => (bool)$configs->get(FtpOption::Passive),
                    'timeout' => 30,
                ]),
            ),
            StrategyKey::Webdav => new WebDAVAdapter(new Client(([
                'baseUri' => $configs->get(WebDavOption::BaseUri),
                'userName' => $configs->get(WebDavOption::Username),
                'password' => $configs->get(WebDavOption::Password),
                'authType' => (int)$configs->get(WebDavOption::AuthType),
            ])), $configs->get(WebDavOption::Prefix) ?: ''),
            StrategyKey::Minio => new AwsS3V3Adapter(
                client: new S3Client([
                    'credentials' => [
                        'key'    => $configs->get(MinioOption::AccessKey),
                        'secret' => $configs->get(MinioOption::SecretKey)
                    ],
                    'endpoint' => $configs->get(MinioOption::Endpoint),
                    'region' => $configs->get(MinioOption::Region) ?: 'us-east-1',
                    'use_path_style_endpoint' => ! $configs->get(MinioOption::BucketEndpoint),
                    'version' => '2006-03-01',
                    'http' => ['connect_timeout' => 5, 'timeout' => 45],
                    'retries' => 2,
                    'bucket_endpoint' => (bool)$configs->get(MinioOption::BucketEndpoint),
                ]),
                bucket: $configs->get(MinioOption::Bucket),
            ),
        };
    }

    private function localRoot(?string $root, bool $managed): string
    {
        $default = config('filesystems.disks.uploads.root');

        return ! $root || $root === $default
            ? config('image.private_root').'/objects'
            : $root;
    }

    /**
     * @throws UploadException
     */
    protected function rateLimiter(Collection $configs, Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();
        $carbon = Carbon::now();
        $array = [
            'minute' => ['key' => GroupConfigKey::LimitPerMinute, 'str' => '分钟'],
            'hours' => ['key' => GroupConfigKey::LimitPerHour, 'str' => '小时'],
            'days' => ['key' => GroupConfigKey::LimitPerDay, 'str' => '天'],
            'weeks' => ['key' => GroupConfigKey::LimitPerWeek, 'str' => '周'],
            'months' => ['key' => GroupConfigKey::LimitPerMonth, 'str' => '月'],
        ];

        foreach ($array as $key => $item) {
            $value = (int) $configs->get($item['key'], 0);
            if ($value < 1) {
                continue;
            }

            $count = Image::query()->whereBetween('created_at', [
                $carbon->parse("-1 {$key}"), $carbon,
            ])->when(! is_null($user), function (Builder $builder) use ($user) {
                $builder->where('user_id', $user->id);
            }, function (Builder $builder) use ($request) {
                $builder->where('uploaded_ip', $request->ip())->whereNull('user_id');
            })->count();

            if ($count >= $value) {
                throw new UploadException("每{$item['str']}内你最多可以上传 {$value} 张图片");
            }
        }
    }

    protected function scan(string $driver, Collection $configs, Image $image, string $path): bool
    {
        try {
            return match ($driver) {
                'tencent' => $this->scanTencent($configs, $path),
                'aliyun' => $this->scanAliyun($configs, $image, $path),
                'nsfwjs' => $this->scanNsfw($configs, $image, $path),
                default => throw new UploadException('未配置内容审核提供方'),
            };
        } catch (UploadException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Utils::e($exception, '内容审核失败');
            throw new UploadException('暂时无法完成内容审核，请稍后重试', previous: $exception);
        }
    }

    private function scanTencent(Collection $configs, string $path): bool
    {
        if (filesize($path) > 5 * 1024 * 1024) {
            throw new UploadException('图片大小超过当前审核提供方的限制');
        }

        $credential = new Credential($configs->get(TencentOption::SecretId), $configs->get(TencentOption::SecretKey));
        $http = new HttpProfile();
        $http->setEndpoint($configs->get(TencentOption::Endpoint));
        $http->setReqTimeout(10);
        $profile = new ClientProfile();
        $profile->setHttpProfile($http);
        $client = new ImsClient($credential, $configs->get(TencentOption::Region), $profile);
        $request = new ImageModerationRequest();
        $request->fromJsonString(json_encode(array_filter([
            'FileContent' => base64_encode(file_get_contents($path)),
            'BizType' => $configs->get(TencentOption::BizType),
        ]), JSON_THROW_ON_ERROR));

        return match ($client->ImageModeration($request)->getSuggestion()) {
            'Pass' => false,
            'Block', 'Review' => true,
            default => throw new \RuntimeException('Invalid Tencent moderation result'),
        };
    }

    private function scanAliyun(Collection $configs, Image $image, string $path): bool
    {
        if (filesize($path) > 20 * 1024 * 1024 || $image->width >= 30000 || $image->height >= 30000) {
            throw new UploadException('图片尺寸超过当前审核提供方的限制');
        }

        $directory = config('image.private_root').'/review';
        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Cannot create moderation work directory');
        }

        $stagedPath = $directory.'/'.$image->key;
        $expiresAt = now()->addMinutes(2);
        $url = URL::temporarySignedRoute('media.review', $expiresAt, ['token' => $image->key]);

        try {
            if (! copy($path, $stagedPath)) {
                throw new \RuntimeException('Unable to stage moderation input');
            }
            Cache::put('media.review.'.$image->key, [$stagedPath, $image->mimetype], $expiresAt);
            AlibabaCloud::accessKeyClient(
                $configs->get(AliyunOption::AccessKeyId),
                $configs->get(AliyunOption::AccessKeySecret),
            )->regionId($configs->get(AliyunOption::RegionId))->asDefaultClient();
            $response = Green::v20180509()->imageSyncScan()->timeout(10)->connectTimeout(3)->body(json_encode([
                'tasks' => [['dataId' => $image->key, 'url' => $url]],
                'scenes' => $configs->get(AliyunOption::Scenes, ['porn']),
                'bizType' => $configs->get(AliyunOption::BizType),
            ], JSON_THROW_ON_ERROR))->request();

            $result = $response->data[0] ?? null;
            if ($response->get('code') != 200 || ($result->code ?? null) != 200 || empty($result->results)) {
                throw new \RuntimeException('Invalid Aliyun moderation result');
            }

            return collect($result->results)->contains(fn ($decision) => ($decision->suggestion ?? null) !== 'pass');
        } finally {
            Cache::forget('media.review.'.$image->key);
            if (is_file($stagedPath)) {
                @unlink($stagedPath);
            }
        }
    }

    private function scanNsfw(Collection $configs, Image $image, string $path): bool
    {
        $stream = fopen($path, 'rb');
        if (! $stream) {
            throw new \RuntimeException('Unable to open moderation input');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(10)->withoutRedirecting()
                ->attach($configs->get(NsfwJsOption::AttrName, 'image'), $stream, $image->origin_name)
                ->post($configs->get(NsfwJsOption::ApiUrl))->throw()->json();
            $threshold = max(1, min(100, (int) $configs->get(NsfwJsOption::Threshold, 60))) / 100;

            foreach (['hentai', 'porn', 'sexy'] as $category) {
                if (! is_numeric($response[$category] ?? null) || $response[$category] < 0 || $response[$category] > 1) {
                    throw new \RuntimeException('Invalid NSFW moderation result');
                }
            }

            return max($response['hentai'], $response['porn'], $response['sexy']) >= $threshold;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function objectName(Collection $configs, UploadedFile $file, string $key, string $extension, ?int $userId): string
    {
        $tokens = [
            '{Y}' => date('Y'),
            '{y}' => date('y'),
            '{m}' => date('m'),
            '{d}' => date('d'),
            '{timestamp}' => time(),
            '{uniqid}' => substr($key, 0, 13),
            '{md5}' => $key,
            '{md5-16}' => substr($key, 0, 16),
            '{str-random-16}' => substr($key, 0, 16),
            '{str-random-10}' => substr($key, 0, 10),
            '{filename}' => $this->safeSegment(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            '{uid}' => $userId ?? 0,
        ];
        $rule = strtr((string) $configs->get(GroupConfigKey::PathNamingRule), $tokens);
        $path = array_values(array_filter(array_map($this->safeSegment(...), explode('/', $rule))));
        $stem = $this->safeSegment(strtr((string) $configs->get(GroupConfigKey::FileNamingRule), $tokens));

        if ($stem === '' || $stem === substr($key, 0, 13) || str_starts_with($key, $stem)) {
            return implode('/', [...array_slice($path, 0, 6), $key.'.'.$extension]);
        }

        return implode('/', [...array_slice($path, 0, 6), $stem.'-'.$key.'.'.$extension]);
    }

    private function safeSegment(string $value): string
    {
        $segment = preg_replace('/[^\p{L}\p{M}\p{N}_.-]+/u', '-', $value) ?? '';

        return trim(mb_strcut($segment, 0, 80), '._-');
    }
}
