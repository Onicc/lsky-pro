<?php

namespace Tests\Feature;

use App\Enums\GroupConfigKey;
use App\Enums\ImagePermission;
use App\Enums\Scan\NsfwJsOption;
use App\Enums\Watermark\Mode;
use App\Models\Group;
use App\Models\Image;
use App\Models\MediaDeletion;
use App\Models\MediaWrite;
use App\Models\Strategy;
use App\Models\User;
use App\Services\ImageService;
use App\Services\UserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Tests\TestCase;

class MediaTest extends TestCase
{
    private string $media;

    protected function setUp(): void
    {
        parent::setUp();

        $this->media = sys_get_temp_dir().'/lsky-media-test-'.bin2hex(random_bytes(8));
        config([
            'image.private_root' => $this->media,
            'image.max_pixels' => 100_000,
        ]);

        $group = Group::query()->where('is_default', true)->firstOrFail();
        $group->configs = $group->configs->merge([
            GroupConfigKey::PathNamingRule => 'album',
            GroupConfigKey::FileNamingRule => '{filename}',
            GroupConfigKey::ImageSaveFormat => 'webp',
        ]);
        $group->save();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->media);

        parent::tearDown();
    }

    public function test_same_named_images_keep_their_distinct_bytes_and_correct_formats(): void
    {
        $owner = User::factory()->create()->refresh();
        $this->actingAs($owner);

        $firstKey = 'test-same-name-0123456789';
        $first = $this->upload(12, 14, $firstKey);
        $second = $this->upload(16, 20, 'test-same-name-9876543210');
        $retry = $this->upload(12, 14, $firstKey);

        $this->assertSame($first->key, $retry->key);
        $this->assertNotSame($first->pathname, $second->pathname);
        $this->assertSame(2, $owner->images()->count());
        $this->assertSame(2, $owner->refresh()->image_num);
        $this->assertSame('same-name.png', $first->origin_name);

        foreach ([$first, $second] as $image) {
            $response = $this->get($image->url)->assertOk()->assertHeader('Content-Type', 'image/webp');
            $bytes = $response->streamedContent();
            $this->assertSame($image->sha256, hash('sha256', $bytes));
            $this->assertSame([$image->width, $image->height, IMAGETYPE_WEBP], array_slice(getimagesizefromstring($bytes), 0, 3));
        }

        $this->post('/upload', ['file' => UploadedFile::fake()->image('same-name.png', 8, 8)], [
            'Idempotency-Key' => $firstKey,
        ])->assertJsonPath('status', false);
        $this->assertSame(2, $owner->images()->count());
    }

    public function test_images_are_only_delivered_by_the_immutable_media_route(): void
    {
        $owner = User::factory()->create()->refresh();
        $this->actingAs($owner);
        $image = $this->upload(12, 14, 'test-route-only-0123456789');

        $this->get($image->url)->assertOk();
        $this->get('/i/same-name.png')->assertNotFound();
    }

    public function test_write_failure_queues_cleanup_without_holding_a_database_transaction(): void
    {
        $owner = User::factory()->create()->refresh();
        $outerLevel = DB::transactionLevel();
        $outcome = new \stdClass();
        $adapter = new class($this->media.'/failing', $outcome) extends LocalFilesystemAdapter {
            public function __construct(string $root, private readonly \stdClass $outcome)
            {
                parent::__construct($root);
            }

            public function writeStream(string $path, $contents, Config $config): void
            {
                $this->outcome->level = DB::transactionLevel();
                $this->outcome->attempted = (bool) MediaWrite::query()->value('write_attempted');
                $this->outcome->visibleImages = Image::query()->count();
                parent::writeStream($path, $contents, $config);

                throw new \RuntimeException('Storage closed while writing test image');
            }
        };
        $this->app->instance(ImageService::class, new class($adapter) extends ImageService {
            public function __construct(private readonly FilesystemAdapter $adapter)
            {
                parent::__construct();
            }

            public function getAdapter(Strategy $strategy, bool $managed = false): FilesystemAdapter
            {
                return $this->adapter;
            }
        });

        $this->actingAs($owner)->post('/upload', [
            'file' => UploadedFile::fake()->image('partially-written.png', 12, 14),
        ])->assertJsonPath('status', false);

        $this->assertSame($outerLevel, $outcome->level);
        $this->assertTrue($outcome->attempted);
        $this->assertSame(0, $outcome->visibleImages);
        $this->assertDatabaseCount('images', 0);
        $this->assertDatabaseCount('media_writes', 0);
        $this->assertDatabaseCount('media_deletions', 1);

        $this->artisan('lsky:media:prune')->assertSuccessful();
        $this->assertDatabaseCount('media_deletions', 0);
        $this->assertCount(0, File::allFiles($this->media.'/failing'));
    }

    public function test_wrong_format_and_excess_pixels_do_not_create_records(): void
    {
        $this->actingAs(User::factory()->create()->refresh());

        $this->post('/upload', ['file' => UploadedFile::fake()->createWithContent('image.jpg', 'wrong format')])
            ->assertJsonPath('status', false);

        $this->post('/upload', ['file' => UploadedFile::fake()->image('photo.png', 500, 300)])
            ->assertJsonPath('status', false);

        $this->assertDatabaseCount('images', 0);
    }

    public function test_heic_is_stored_and_downloaded_in_its_native_format_and_viewed_as_webp(): void
    {
        $owner = User::factory()->create()->refresh();
        $group = $owner->group;
        $group->configs = $group->configs->put(GroupConfigKey::ImageSaveFormat, '');
        $group->save();

        $source = base_path('tests/Fixtures/photo.heic');
        $links = $this->actingAs($owner)->post('/upload', [
            'file' => new UploadedFile($source, 'photo.HEIC', 'application/octet-stream', test: true),
            'permission' => ImagePermission::Private,
        ])->assertJsonPath('status', true)->assertJsonPath('data.mimetype', 'image/heic')->json('data.links');

        $image = $owner->images()->firstOrFail();
        $this->assertSame([72, 48, 'heic'], [$image->width, $image->height, $image->extension]);
        $stored = $image->filesystem()->read($image->storagePath());
        $this->assertSame('image/heic', (new \finfo(FILEINFO_MIME_TYPE))->buffer($stored));
        $this->assertSame($image->sha256, hash('sha256', $stored));
        $download = $this->get($links['original_url'])->assertOk()->assertHeader('Content-Type', 'image/heic');
        $this->assertStringContainsString('attachment;', $download->headers->get('Content-Disposition'));
        $this->assertSame($stored, $download->streamedContent());

        $preview = $this->get($links['url'])->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->assertSame([72, 48, IMAGETYPE_WEBP], array_slice(getimagesizefromstring($preview->getContent()), 0, 3));
        $thumb = $this->get($links['thumbnail_url'])->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->assertSame([72, 48, IMAGETYPE_WEBP], array_slice(getimagesize($thumb->baseResponse->getFile()->getPathname()), 0, 3));

        Auth::guard('web')->logout();
        foreach ([$links['original_url'], $links['url'], $links['thumbnail_url']] as $link) {
            $this->get($link)->assertNotFound();
        }
    }

    public function test_heic_api_respects_source_preservation_and_group_conversion(): void
    {
        $owner = User::factory()->create()->refresh();
        $group = $owner->group;
        $token = $owner->createToken('HEIC test', ['images:write'])->plainTextToken;
        $source = base_path('tests/Fixtures/photo.heic');
        $upload = fn () => $this->post('/api/v1/upload', [
            'file' => new UploadedFile($source, 'photo.heic', test: true),
        ], ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]);

        $group->configs = $group->configs->merge([
            GroupConfigKey::ImageSaveFormat => '',
            GroupConfigKey::ImageSaveQuality => 100,
        ]);
        $group->save();
        config(['image.strip_metadata' => false]);

        $key = $upload()->assertJsonPath('status', true)->assertJsonPath('data.extension', 'heic')->json('data.key');
        $image = Image::query()->where('key', $key)->firstOrFail();
        $this->assertSame(file_get_contents($source), $image->filesystem()->read($image->storagePath()));

        $group->configs = $group->configs->put(GroupConfigKey::ImageSaveFormat, 'webp');
        $group->save();
        $this->app['auth']->forgetGuards();
        $upload()->assertJsonPath('status', true)->assertJsonPath('data.extension', 'webp')
            ->assertJsonMissingPath('data.links.original_url');
    }

    public function test_heic_rejects_wrong_content_excess_pixels_and_disallowed_groups(): void
    {
        $owner = User::factory()->create()->refresh();
        $this->actingAs($owner);
        $source = base_path('tests/Fixtures/photo.heic');
        $upload = fn () => $this->post('/upload', ['file' => new UploadedFile($source, 'photo.heic', test: true)]);
        $this->post('/upload', ['file' => UploadedFile::fake()->image('photo.png', 12, 12)])
            ->assertJsonPath('status', true);
        $this->post('/upload', ['file' => UploadedFile::fake()->createWithContent('photo.heic', 'wrong format')])
            ->assertJsonPath('status', false);
        config(['image.max_pixels' => 3000]);
        $upload()->assertJsonPath('status', false)->assertJsonPath('message', '图片像素数量超出限制');

        $group = $owner->group;
        $group->configs = $group->configs->put(GroupConfigKey::AcceptedFileSuffixes, ['png']);
        $group->save();
        $upload()->assertJsonPath('status', false)->assertJsonPath('message', '不支持的文件类型');
        $this->assertDatabaseCount('images', 1);
    }

    public function test_heic_is_moderated_as_jpeg_before_storage(): void
    {
        $owner = User::factory()->create()->refresh();
        $group = $owner->group;
        $url = 'https://moderation.example.test/classify';
        $group->configs = $group->configs->merge([
            GroupConfigKey::ImageSaveFormat => '',
            GroupConfigKey::IsEnableScan => true,
            GroupConfigKey::ScannedAction => 'delete',
            GroupConfigKey::ScanConfigs => [
                'driver' => 'nsfwjs',
                'drivers' => ['nsfwjs' => [NsfwJsOption::ApiUrl => $url]],
            ],
        ]);
        $group->save();
        $reviewedAsJpeg = [];
        Http::fake([$url => function ($request) use (&$reviewedAsJpeg) {
            $body = $request->body();
            $reviewedAsJpeg[] = str_contains($body, 'filename="photo.jpg"') && str_contains($body, "\xff\xd8\xff");

            return Http::response(['hentai' => 0, 'porn' => count($reviewedAsJpeg) - 1, 'sexy' => 0]);
        }]);
        $source = base_path('tests/Fixtures/photo.heic');
        $this->actingAs($owner);
        $upload = fn () => $this->post('/upload', ['file' => new UploadedFile($source, 'photo.heic', test: true)]);

        $upload()->assertJsonPath('status', true)->assertJsonPath('data.mimetype', 'image/heic');
        $upload()->assertJsonPath('status', false)->assertJsonPath('message', '图片涉嫌违规，禁止上传');
        $this->assertSame([true, true], $reviewedAsJpeg);
        $this->assertDatabaseCount('images', 1);
    }

    public function test_private_media_and_thumbnails_require_access_and_short_shares_can_be_revoked(): void
    {
        $owner = User::factory()->create()->refresh();
        $this->actingAs($owner);
        $image = $this->upload(12, 14, 'test-private-0123456789', ImagePermission::Private);
        $this->get($image->url)->assertOk();
        $this->get($image->thumb_url)->assertOk()->assertHeader('Content-Type', 'image/webp');

        $share = $this->postJson(route('user.image.share', ['id' => $image->id]))
            ->assertOk()->assertJsonPath('status', true)->json('data.url');

        Auth::guard('web')->logout();
        $this->get($image->url)->assertNotFound();
        $this->get($image->thumb_url)->assertNotFound();
        $this->get($share)->assertOk();

        $this->actingAs($owner)->putJson(route('user.images.permission'), [
            'permission' => 'private',
            'ids' => [$image->id],
        ])->assertJsonPath('status', true);

        Auth::guard('web')->logout();
        $this->get($share)->assertNotFound();
    }

    public function test_deleted_images_are_revoked_and_pruned(): void
    {
        $owner = User::factory()->create()->refresh();
        $this->actingAs($owner);
        $image = $this->upload(12, 14, 'test-prune-0123456789');
        $this->assertNotNull($image->thumbnail_pending_at);
        $this->assertFileDoesNotExist($image->thumbnailPath());

        $this->artisan('lsky:thumbnails --pending')->assertSuccessful();
        $this->assertNull($image->refresh()->thumbnail_pending_at);
        $this->get($image->thumb_url)->assertOk();

        $this->deleteJson(route('user.images.delete'), [
            'ids' => [$image->id],
        ])->assertJsonPath('data.deleted', 1);

        $this->get($image->url)->assertNotFound();
        $this->assertTrue($image->filesystem()->fileExists($image->storagePath()));
        $this->assertDatabaseHas('media_deletions', ['image_key' => $image->key]);

        $this->artisan('lsky:media:prune')->assertSuccessful();
        $this->assertFalse($image->filesystem()->fileExists($image->storagePath()));
        $this->assertFileDoesNotExist($image->thumbnailPath());
        $this->assertSame(0, MediaDeletion::query()->count());
    }

    public function test_dynamic_image_watermarks_do_not_change_original_storage(): void
    {
        $path = storage_path('app/public/watermark-'.bin2hex(random_bytes(8)).'.png');
        File::ensureDirectoryExists(dirname($path));
        $logo = imagecreatetruecolor(4, 4);
        imagefilledrectangle($logo, 0, 0, 3, 3, imagecolorallocate($logo, 255, 0, 0));
        imagepng($logo, $path);

        try {
            $owner = User::factory()->create()->refresh();
            $group = $owner->group;
            $group->configs = $group->configs->merge([
                GroupConfigKey::ImageSaveFormat => 'png',
                GroupConfigKey::IsEnableWatermark => true,
                GroupConfigKey::WatermarkConfigs => [
                    'mode' => Mode::Dynamic,
                    'driver' => 'image',
                    'drivers' => ['image' => [
                        'image' => basename($path), 'position' => 'top-left', 'x' => 0, 'y' => 0, 'opacity' => 100,
                    ]],
                ],
            ]);
            $group->save();

            $this->actingAs($owner);
            $image = $this->upload(12, 14, 'test-watermark-0123456789');
            $delivered = $this->get($image->url)->assertOk()->getContent();
            $stored = $image->filesystem()->read($image->storagePath());
            $this->assertNotSame($stored, $delivered);
            $this->assertSame(0xff0000, imagecolorat(imagecreatefromstring($delivered), 0, 0) & 0xffffff);
        } finally {
            File::delete($path);
        }
    }

    public function test_storage_secrets_are_encrypted_and_not_serialized(): void
    {
        $strategy = Strategy::query()->firstOrFail();
        $strategy->configs = $strategy->configs->put('password', 'local-only-fixture');
        $strategy->save();

        $fresh = $strategy->fresh();
        $this->assertSame('local-only-fixture', $fresh->configs->get('password'));
        $this->assertStringNotContainsString('local-only-fixture', $fresh->getRawOriginal('configs'));
        $this->assertArrayNotHasKey('configs', $fresh->toArray());
    }

    private function upload(int $width, int $height, string $key, int $permission = ImagePermission::LinkOnly): Image
    {
        $id = $this->post('/upload', [
            'file' => UploadedFile::fake()->image('same-name.png', $width, $height),
            'permission' => $permission,
        ], ['Idempotency-Key' => $key])->assertOk()->assertJsonPath('status', true)->json('data.id');

        return Image::query()->findOrFail($id);
    }
}
