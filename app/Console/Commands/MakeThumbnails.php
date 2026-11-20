<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\MediaThumbnails;
use Illuminate\Console\Command;

class MakeThumbnails extends Command
{
    protected $signature = 'lsky:thumbnails
        {--force : Rebuild previously generated thumbnails}
        {--pending : Process only due pending thumbnails}
        {--limit=0 : Limit the number of processed images}';

    protected $description = 'Build private image thumbnails';

    public function handle(MediaThumbnails $thumbnails): int
    {
        $failed = 0;
        $processed = 0;
        $limit = max(0, (int) $this->option('limit'));
        $query = Image::query()->with('group', 'strategy')->whereNotNull('strategy_id')->where('extension', '<>', 'svg');
        if ($this->option('pending')) {
            $query->where('thumbnail_pending_at', '<=', now());
        }

        foreach ($query->lazyById(100) as $image) {
            if ($limit && $processed >= $limit) {
                break;
            }
            ++$processed;

            try {
                $thumbnails->make($image, (bool) $this->option('force'));
            } catch (\Throwable $exception) {
                ++$failed;
                report($exception);
                $this->warn("Could not build thumbnail for image {$image->key}.");
                Image::query()->whereKey($image->id)->whereNotNull('thumbnail_pending_at')->increment('thumbnail_attempts', extra: [
                    'thumbnail_pending_at' => now()->addMinutes(min(1440, 2 ** min(11, $image->thumbnail_attempts))),
                ]);
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
