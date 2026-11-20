<?php

namespace App\Console\Commands;

use App\Enums\StrategyKey;
use App\Models\Image;
use App\Models\Strategy;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class AuditMedia extends Command
{
    protected $signature = 'lsky:media:audit
        {--strategy= : Inspect one storage strategy by its numeric ID}
        {--after-id=0 : Resume after an already inspected image ID}
        {--limit=0 : Maximum images to inspect, zero scans all}
        {--metadata-only : Inspect shared paths without reading storage objects}';

    protected $description = 'Audit legacy media references and file integrity without writing data';

    public function handle(): int
    {
        $strategyId = $this->option('strategy');
        $after = $this->option('after-id');
        $limit = $this->option('limit');

        if (($strategyId !== null && ! ctype_digit((string) $strategyId))
            || ! ctype_digit((string) $after) || ! ctype_digit((string) $limit)) {
            $this->error('Strategy, after-id and limit must be non-negative integers.');

            return self::INVALID;
        }

        $locations = Strategy::query()->get()->groupBy(function (Strategy $strategy) {
            $location = $strategy->storageLocation();
            if ($strategy->key === StrategyKey::Local) {
                $root = $location['root'] ?? config('filesystems.disks.uploads.root');
                $location = ['root' => realpath($root) ?: rtrim($root, '/')];
            }

            return hash('sha256', json_encode([$strategy->key, $location], JSON_THROW_ON_ERROR));
        });

        $namespace = [];
        foreach ($locations as $strategies) {
            foreach ($strategies as $strategy) {
                $namespace[$strategy->id] = $strategies->pluck('id')->all();
            }
        }

        $checked = 0;
        $lastId = (int) $after;
        $findings = [];
        $query = Image::query()->with('strategy')->where('object_managed', false)
            ->when($strategyId, fn (Builder $query) => $query->where('strategy_id', $strategyId));
        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        }

        foreach ($query->lazyById(100) as $image) {
            if ($limit > 0 && $checked >= $limit) {
                break;
            }
            ++$checked;
            $lastId = $image->id;

            foreach ($this->inspect($image, $namespace[$image->strategy_id] ?? []) as $finding) {
                $findings[$finding] = ($findings[$finding] ?? 0) + 1;
                $this->line(json_encode([
                    'type' => $finding,
                    'image_id' => $image->id,
                    'strategy_id' => $image->strategy_id,
                ], JSON_THROW_ON_ERROR));
            }
        }

        $this->line(json_encode([
            'type' => 'summary',
            'checked' => $checked,
            'last_image_id' => $lastId,
            'findings' => (object) $findings,
        ], JSON_THROW_ON_ERROR));

        return $findings ? self::FAILURE : self::SUCCESS;
    }

    private function inspect(Image $image, array $strategyIds): array
    {
        if (! $image->strategy) {
            return ['strategy_missing'];
        }

        $path = ltrim($image->path, '/');
        $paths = $path === '' || $path === '.' ? ['', '.', '/'] : [$path, '/'.$path];
        $conflict = Image::query()->where('object_managed', false)->whereIn('strategy_id', $strategyIds)
            ->where('name', $image->name)->whereIn('path', $paths)
            ->where(fn (Builder $query) => $query->where('md5', '<>', $image->md5)
                ->orWhere('sha1', '<>', $image->sha1))->exists();
        $findings = $conflict ? ['conflicting_references'] : [];

        if ($this->option('metadata-only')) {
            return $findings;
        }

        try {
            $filesystem = $image->filesystem();
            if (! $filesystem->fileExists($image->storagePath())) {
                return [...$findings, 'object_missing'];
            }

            $stream = $filesystem->readStream($image->storagePath());
            try {
                $md5 = hash_init('md5');
                $sha1 = hash_init('sha1');
                while (! feof($stream)) {
                    $bytes = fread($stream, 65536);
                    if ($bytes === false || ($bytes === '' && ! feof($stream))) {
                        return [...$findings, 'object_unreadable'];
                    }
                    hash_update($md5, $bytes);
                    hash_update($sha1, $bytes);
                }

                if (strtolower($image->md5) !== hash_final($md5) || strtolower($image->sha1) !== hash_final($sha1)) {
                    $findings[] = 'digest_mismatch';
                }
            } finally {
                fclose($stream);
            }
        } catch (\Throwable) {
            $findings[] = 'object_unreadable';
        }

        return $findings;
    }
}
