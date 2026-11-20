<?php

namespace App\Http\Controllers;

use App\Enums\ImagePermission;
use App\Enums\StrategyKey;
use App\Enums\UserStatus;
use App\Enums\Watermark\Mode;
use App\Models\Image;
use App\Models\Strategy;
use App\Services\ImageProcessor;
use App\Services\MediaThumbnails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MediaController extends Controller
{
    public function original(Request $request, string $key, string $extension, ImageProcessor $processor): Response
    {
        $image = Image::query()->with('group', 'strategy')
            ->where('key', $key)->where('extension', strtolower($extension))->firstOrFail();

        return $this->deliver($request, $image, $processor);
    }

    public function legacy(Request $request, string $prefix, string $file, ImageProcessor $processor): Response
    {
        $requestPath = $prefix.'/'.$file;
        $images = (new Image())->newCollection();
        foreach (Strategy::query()->where('key', StrategyKey::Local)->get() as $strategy) {
            $publicPrefix = trim(rawurldecode((string) parse_url((string) $strategy->configs->get('url'), PHP_URL_PATH)), '/');
            if ($publicPrefix === '' || ! str_starts_with($requestPath, $publicPrefix.'/')) {
                continue;
            }

            $pathname = substr($requestPath, strlen($publicPrefix) + 1);
            $directory = dirname($pathname);
            $matches = Image::query()->with('group')->where('strategy_id', $strategy->id)
                ->where('object_managed', false)->where('name', basename($pathname))
                ->whereIn('path', $directory === '.' ? ['', '.'] : [$directory, '/'.$directory])
                ->get()->each(fn (Image $image) => $image->setRelation('strategy', $strategy));
            $images = $images->merge($matches);
        }

        // A legacy physical path can be shared by several records. Each record must allow this raw path.
        abort_if($images->isEmpty() || $images->contains(fn (Image $image) => ! $this->canView($request, $image)), 404);

        return $this->deliver($request, $images->first(), $processor);
    }

    public function thumbnail(Request $request, string $key, MediaThumbnails $thumbnails): Response
    {
        $image = Image::query()->with('group', 'strategy')->where('key', $key)->firstOrFail();
        abort_unless($this->canView($request, $image) && $image->extension !== 'svg', 404);

        try {
            return response()->file($thumbnails->make($image), $this->headers('image/webp'));
        } catch (\Throwable $exception) {
            report($exception);
            abort(404);
        }
    }

    public function review(Request $request, string $token): Response
    {
        abort_unless($request->hasValidSignature(), 404);
        $review = Cache::get('media.review.'.$token);
        abort_unless(is_array($review) && is_readable($review[0]), 404);

        return response()->file($review[0], $this->headers($review[1]));
    }

    private function deliver(Request $request, Image $image, ImageProcessor $processor): Response
    {
        abort_unless($this->canView($request, $image), 404);

        try {
            $stream = $image->filesystem()->readStream($image->storagePath());
        } catch (FilesystemException $exception) {
            report($exception);
            abort(404);
        }

        $options = $image->group?->configs ?? collect();
        $activeType = ! in_array($image->extension, ImageProcessor::SUPPORTED_EXTENSIONS, true);
        $dynamic = ! $activeType && ! in_array($image->extension, ['ico', 'gif'], true)
            && $processor->watermarkConfig($options, Mode::Dynamic);
        $rasterize = in_array($image->extension, ['psd', 'tif', 'tiff', 'bmp'], true);

        if ($dynamic || $rasterize) {
            try {
                $encoded = $processor->render($stream, $image->extension, $options);
            } catch (\Throwable $exception) {
                report($exception);
                abort(404);
            } finally {
                fclose($stream);
            }

            return response($encoded->toString(), 200, $this->headers($encoded->mediaType()));
        }

        // Legacy metadata may contain an incorrect extension after a historical conversion.
        $verified = in_array($image->mimetype, ['image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'image/vnd.microsoft.icon', 'image/x-icon'], true);
        $headers = $this->headers($activeType || ! $verified ? 'application/octet-stream' : $image->mimetype);
        $response = response()->stream(static function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, $headers);

        if ($activeType || ! $verified) {
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                str_replace(['%', '/', '\\'], '-', basename($image->name)),
                'image-download',
            ));
        }

        return $response;
    }

    private function canView(Request $request, Image $image): bool
    {
        $user = $request->user();
        $activeAccount = $user && $user->status === UserStatus::Normal;
        if ($image->is_unhealthy) {
            return $activeAccount && $user->is_adminer;
        }

        return $image->permission !== ImagePermission::Private
            || ($activeAccount && ($user->id === $image->user_id || $user->is_adminer))
            || ($request->routeIs('media.original') && $request->hasValidSignature()
                && is_string($request->query('version'))
                && hash_equals((string) $image->share_version, $request->query('version')));
    }

    private function headers(string $mime): array
    {
        return [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Referrer-Policy' => 'no-referrer',
        ];
    }
}
