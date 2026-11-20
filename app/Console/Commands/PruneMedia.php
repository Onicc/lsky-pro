<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\MediaDeletion;
use App\Models\MediaWrite;
use App\Models\Strategy;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Filesystem;

class PruneMedia extends Command
{
    protected $signature = 'lsky:media:prune {--limit=100 : Maximum queued deletions to process}';

    protected $description = 'Remove unreferenced media objects and cached images';

    public function handle(ImageService $media): int
    {
        $this->pruneExpiredReviews();
        $this->retireExpiredWrites();
        $ids = MediaDeletion::query()->where('available_at', '<=', now())
            ->orderBy('id')->limit(max(1, (int) $this->option('limit')))->pluck('id');
        $failed = 0;

        foreach ($ids as $id) {
            try {
                $record = MediaDeletion::query()->find($id);
                if (! $record) {
                    continue;
                }

                Cache::lock('media.thumbnail.'.$record->image_key, 120)->block(10, function () use ($media, $id) {
                    $current = MediaDeletion::query()->whereKey($id)->where('available_at', '<=', now())->first();
                    if ($current) {
                        $this->prune($current, $media);
                    }
                });
            } catch (\Throwable $exception) {
                ++$failed;
                report($exception);
                $record = MediaDeletion::query()->find($id);
                if ($record) {
                    $record->increment('attempts', extra: [
                        'available_at' => now()->addMinutes(min(1440, 2 ** min(11, $record->attempts))),
                    ]);
                }
                $this->warn("Cleanup is queued for retry for operation {$id}.");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function retireExpiredWrites(): void
    {
        $ids = MediaWrite::query()->where('expires_at', '<=', now())
            ->orderBy('id')->limit(max(1, (int) $this->option('limit')))->pluck('id');

        foreach ($ids as $id) {
            DB::transaction(function () use ($id) {
                $reservation = MediaWrite::query()->whereKey($id)->where('expires_at', '<=', now())->lockForUpdate()->first();
                $reservation?->retire();
            }, 3);
        }
    }

    private function pruneExpiredReviews(): void
    {
        $directory = config('image.private_root').'/review';
        if (! is_dir($directory)) {
            return;
        }

        $cutoff = now()->subMinutes(10)->getTimestamp();
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $file) {
            if ($file->isFile() && preg_match('/\A[a-f0-9]{32}\z/', $file->getFilename()) && $file->getMTime() < $cutoff) {
                @unlink($file->getPathname());
            }
        }
    }

    private function prune(MediaDeletion $record, ImageService $media): void
    {
        $strategy = $record->strategy_id ? Strategy::query()->find($record->strategy_id) : null;

        // Older strategies can point at the same physical storage, so conservatively retain matching paths.
        $directory = ltrim($record->path, '/');
        $paths = $directory === '' || $directory === '.' ? ['', '.', '/'] : [$directory, '/'.$directory];
        $stillReferenced = Image::query()->where('object_managed', $record->object_managed)
            ->whereIn('path', $paths)->where('name', $record->name)->exists();

        if ($strategy && ! $stillReferenced) {
            $image = new Image(['path' => $record->path, 'name' => $record->name]);
            $image->object_managed = $record->object_managed;
            $storage = new Filesystem($media->getAdapter($strategy, $record->object_managed));
            $storage->delete($image->storagePath());
        }

        $root = config('image.private_root').'/thumbnails';
        if (preg_match('/\A[a-zA-Z0-9]+\z/', $record->image_key)) {
            foreach (glob($root.'/'.$record->image_key.'{.webp,-*.webp}', GLOB_BRACE) ?: [] as $file) {
                if (! @unlink($file) && is_file($file)) {
                    throw new \RuntimeException('Could not remove image thumbnail');
                }
            }
        }

        $record->delete();
    }
}
