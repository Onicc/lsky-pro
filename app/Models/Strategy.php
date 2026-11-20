<?php

namespace App\Models;

use App\Casts\StrategyConfig;
use App\Enums\StrategyKey;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Sabre\DAV\Client;

/**
 * @property int $id
 * @property int $key
 * @property string $name
 * @property string $intro
 * @property \Illuminate\Support\Collection $configs
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property-read Collection $groups
 * @property-read Collection $images
 */
class Strategy extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'intro',
        'configs',
    ];

    protected $attributes = [
        'intro' => '',
    ];

    protected $casts = [
        'id' => 'integer',
        'key' => 'integer',
        'configs' => StrategyConfig::class,
    ];

    protected $hidden = ['configs'];

    const DRIVERS = [
        StrategyKey::Local => '本地',
        StrategyKey::S3 => 'AWS S3',
        StrategyKey::Oss => '阿里云 OSS',
        StrategyKey::Cos => '腾讯云 COS',
        StrategyKey::Kodo => '七牛云 Kodo',
        StrategyKey::Uss => '又拍云 USS',
        StrategyKey::Sftp => 'SFTP',
        StrategyKey::Ftp => 'FTP',
        StrategyKey::Webdav => 'WebDav',
        StrategyKey::Minio => 'Minio',
    ];

    const WEBDAV_AUTH_TYPES = [
        '' => 'Auto',
        Client::AUTH_BASIC => 'Basic',
        Client::AUTH_DIGEST => 'Digest',
        Client::AUTH_NTLM => 'Ntlm',
    ];

    public function supportsPrivateMedia(): bool
    {
        $root = $this->configs->get('root');
        $defaultLocal = $this->key === StrategyKey::Local
            && (! $root || $root === config('filesystems.disks.uploads.root'));

        return $defaultLocal || (bool) $this->configs->get('private_objects', false);
    }

    public function isLegacyDeliveryManaged(): bool
    {
        return $this->key === StrategyKey::Local
            && parse_url((string) $this->configs->get('url'), PHP_URL_HOST)
                === parse_url((string) config('app.url'), PHP_URL_HOST);
    }

    public function storageLocation(): array
    {
        return $this->configs->only([
            'root', 'endpoint', 'region', 'bucket', 'bucket_endpoint', 'use_path_style_endpoint',
            'service', 'app_id', 'host', 'port', 'username', 'base_uri', 'prefix',
        ])->reject(fn ($value) => $value === null || $value === '')
            ->map(fn ($value) => (string) $value)->sortKeys()->all();
    }

    public static function getRootPath(?string $url): string
    {
        $path = trim((string) parse_url($url ?? '', PHP_URL_PATH), '/');

        return explode('/', $path, 2)[0];
    }

    public function intro(): Attribute
    {
        return new Attribute(
            set: fn ($value) => $value ?: '',
        );
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_strategy', 'strategy_id', 'group_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'strategy_id', 'id');
    }
}
