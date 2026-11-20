<?php

namespace App\Services;

use App\Enums\Watermark\Mode;
use App\Models\Image;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class MediaThumbnails
{
    public function __construct(private readonly ImageProcessor $processor)
    {
    }

    public function make(Image $image, bool $force = false): string
    {
        $target = $image->thumbnailPath();
        $settings = $image->group?->configs ?? collect();
        if ($watermark = $this->processor->watermarkConfig($settings, Mode::Dynamic)) {
            // Key dynamic thumbnails by the watermark settings.
            $target = substr($target, 0, -5).'-'.hash('sha256', serialize($watermark->all())).'.webp';
        }

        if (! $force && is_file($target)) {
            $this->complete($image);

            return $target;
        }

        return Cache::lock('media.thumbnail.'.$image->key, 90)
            ->block(10, function () use ($image, $settings, $target, $force) {
                if ($force || ! is_file($target)) {
                    if (! Image::query()->whereKey($image->id)->exists()) {
                        throw new \RuntimeException('Image was removed before thumbnail generation');
                    }
                    $this->generate($image, $settings, $target);
                }
                $this->complete($image);

                return $target;
            });
    }

    private function generate(Image $image, Collection $settings, string $target): void
    {
        File::ensureDirectoryExists(dirname($target));
        $temporaryPath = tempnam(dirname($target), 'thumbnail-');
        if ($temporaryPath === false) {
            throw new \RuntimeException('Cannot create thumbnail work file');
        }

        try {
            $stream = $image->filesystem()->readStream($image->storagePath());
            try {
                $this->processor->thumbnail($stream, $settings)->save($temporaryPath);
            } finally {
                fclose($stream);
            }

            if (! rename($temporaryPath, $target)) {
                throw new \RuntimeException('Cannot save thumbnail');
            }
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    private function complete(Image $image): void
    {
        if ($image->thumbnail_pending_at) {
            Image::query()->whereKey($image->id)->whereNotNull('thumbnail_pending_at')->update([
                'thumbnail_pending_at' => null,
                'thumbnail_attempts' => 0,
            ]);
        }
    }
}
