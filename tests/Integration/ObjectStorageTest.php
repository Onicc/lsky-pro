<?php

namespace Tests\Integration;

use App\Enums\ImagePermission;
use App\Enums\StrategyKey;
use App\Models\Image;
use App\Models\Strategy;
use App\Models\User;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ObjectStorageTest extends TestCase
{
    public function test_s3_and_minio_adapters_store_serve_and_prune_private_objects(): void
    {
        $endpoint = getenv('LSKY_TEST_S3_ENDPOINT');
        $accessKey = getenv('LSKY_TEST_S3_ACCESS_KEY');
        $secret = getenv('LSKY_TEST_S3_SECRET_KEY');
        if (! $endpoint || ! $accessKey || ! $secret) {
            $this->markTestSkipped('Set the test object storage endpoint and credentials.');
        }

        $bucket = 'lsky-test-'.bin2hex(random_bytes(5));
        $region = 'us-east-1';
        $client = new S3Client([
            'endpoint' => $endpoint,
            'credentials' => ['key' => $accessKey, 'secret' => $secret],
            'region' => $region,
            'version' => '2006-03-01',
            'use_path_style_endpoint' => true,
        ]);
        $client->createBucket(['Bucket' => $bucket]);

        try {
            $this->verifyStrategy(StrategyKey::S3, $endpoint, $region, $bucket, [
                'access_key_id' => $accessKey,
                'secret_access_key' => $secret,
                'use_path_style_endpoint' => true,
            ]);
            $this->verifyStrategy(StrategyKey::Minio, $endpoint, $region, $bucket, [
                'access_key' => $accessKey,
                'secret_key' => $secret,
            ]);
        } finally {
            foreach ($client->listObjectsV2(['Bucket' => $bucket])['Contents'] ?? [] as $object) {
                $client->deleteObject(['Bucket' => $bucket, 'Key' => $object['Key']]);
            }
            $client->deleteBucket(['Bucket' => $bucket]);
        }
    }

    private function verifyStrategy(int $driver, string $endpoint, string $region, string $bucket, array $credentials): void
    {
        $user = User::factory()->create()->refresh();
        $strategy = Strategy::query()->create([
            'key' => $driver,
            'name' => 'Test object storage',
            'configs' => $credentials + [
                'endpoint' => $endpoint,
                'region' => $region,
                'bucket' => $bucket,
                'private_objects' => true,
                'url' => $endpoint.'/'.$bucket,
            ],
        ]);

        $user->group->strategies()->attach($strategy);

        $id = $this->actingAs($user)->post('/upload', [
            'file' => UploadedFile::fake()->image('photo.png', 12, 14),
            'strategy_id' => $strategy->id,
            'permission' => ImagePermission::Private,
        ])->assertOk()->assertJsonPath('status', true)->json('data.id');

        $image = Image::query()->findOrFail($id);
        $this->assertTrue($image->filesystem()->fileExists($image->storagePath()));
        $body = $this->get($image->url)->assertOk()->streamedContent();
        $this->assertSame($image->sha256, hash('sha256', $body));

        $this->deleteJson(route('user.images.delete'), ['ids' => [$image->id]])
            ->assertOk()->assertJsonPath('data.deleted', 1);
        $this->artisan('lsky:media:prune')->assertSuccessful();
        $this->assertFalse($image->filesystem()->fileExists($image->storagePath()));
    }
}
