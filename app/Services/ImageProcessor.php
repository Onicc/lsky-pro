<?php

namespace App\Services;

use App\Enums\GroupConfigKey;
use App\Enums\Watermark\FontOption;
use App\Enums\Watermark\ImageOption;
use App\Enums\Watermark\Mode;
use App\Exceptions\UploadException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Alignment;
use Intervention\Image\Color;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Exceptions\ImageException;
use Intervention\Image\Exceptions\RuntimeException;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ImageManagerInterface;
use Intervention\Image\Typography\FontFactory;

class ImageProcessor
{
    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp', 'ico', 'psd', 'webp', 'heic'];

    private const LARGE_IMAGE_PIXELS = 16_000_000;

    private const IMAGE_TYPES = [
        'jpg' => [IMAGETYPE_JPEG],
        'jpeg' => [IMAGETYPE_JPEG],
        'png' => [IMAGETYPE_PNG],
        'gif' => [IMAGETYPE_GIF],
        'tif' => [IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM],
        'tiff' => [IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM],
        'bmp' => [IMAGETYPE_BMP],
        'ico' => [IMAGETYPE_ICO],
        'psd' => [IMAGETYPE_PSD],
        'webp' => [IMAGETYPE_WEBP],
    ];

    public function inspect(string $path, string $extension): array
    {
        if ($extension === 'heic') {
            [$width, $height] = $this->inspectHeic($path);
            $mime = 'image/heic';
            $bits = 0;
        } else {
            $info = @getimagesize($path);

            if (! $info || ! in_array($info[2], self::IMAGE_TYPES[$extension] ?? [], true)) {
                throw new UploadException('图片内容与文件格式不一致或已损坏');
            }

            [$width, $height] = $info;
            $mime = $info['mime'];
            $bits = $info['bits'] ?? 0;
        }

        $maximumPixels = max(1, (int) config('image.max_pixels'));

        if ($width < 1 || $height < 1 || $width > intdiv($maximumPixels, $height)) {
            throw new UploadException('图片像素数量超出限制');
        }

        return [$width, $height, $mime, $bits];
    }

    private function inspectHeic(string $path): array
    {
        if ((new \finfo(FILEINFO_MIME_TYPE))->file($path) !== 'image/heic') {
            throw new UploadException('图片内容与文件格式不一致或已损坏');
        }

        $native = $this->heicDecoder();
        try {
            $native->pingImage('HEIC:'.$path);
            if ($native->getImageFormat() !== 'HEIC' || $native->getNumberImages() !== 1) {
                throw new UploadException('目前仅支持单张 HEIC 照片');
            }

            return [$native->getImageWidth(), $native->getImageHeight()];
        } catch (\ImagickException $exception) {
            throw new UploadException('无法读取 HEIC 图片，文件可能已损坏', previous: $exception);
        } finally {
            $native->clear();
        }
    }

    public function prepare(string $path, string $extension, Collection $configs): array
    {
        [$width, $height, , $bits] = $this->inspect($path, $extension);

        return $this->withPixelBudget($width, $height, fn () =>
            $this->prepareFile($path, $extension, $configs, $width, $height, $bits));
    }

    public function withPixelBudget(int $width, int $height, \Closure $process): mixed
    {
        if ($width * $height < self::LARGE_IMAGE_PIXELS) {
            return $process();
        }

        try {
            return Cache::lock('media.processing.large', 120)->block(15, $process);
        } catch (LockTimeoutException $exception) {
            throw new UploadException('服务器正在处理其他大图，请稍后重试', previous: $exception);
        }
    }

    private function prepareFile(
        string $path,
        string $extension,
        Collection $configs,
        int $width,
        int $height,
        int $bits,
    ): array {
        $watermark = $this->watermarkConfig($configs, Mode::Overlay);
        $quality = max(1, min(100, (int) $configs->get(GroupConfigKey::ImageSaveQuality, 75)));
        $requestedFormat = strtolower((string) $configs->get(GroupConfigKey::ImageSaveFormat));
        $target = $requestedFormat ?: $extension;
        $strip = (bool) config('image.strip_metadata');

        if (config('image.driver') === 'gd' && $extension === 'png') {
            $this->checkGdPngMemory($width * $height);
        }
        try {
            $image = $extension === 'heic' ? $this->decodeHeic($path) : $this->manager()->decodePath($path);
        } catch (RuntimeException $exception) {
            throw new UploadException('图片无法解码，文件可能已损坏或超出处理资源限制', previous: $exception);
        }

        $this->checkFrames($image);
        if (in_array($extension, ['gif', 'ico', 'psd'], true)
            || (! $watermark && $quality === 100 && $requestedFormat === '' && ! $strip)) {
            return [$extension, null];
        }

        // The Imagick PNG coder can require three large pixel caches while encoding.
        $streamPng = $width * $height >= self::LARGE_IMAGE_PIXELS && $bits > 0 && $bits <= 8
            && $extension === 'png' && $target === 'png' && $strip && ! $watermark
            && $image->count() === 1 && $this->isSimplePng($path);

        $format = Format::tryCreate($target);
        $encodeHeic = $extension === 'heic' && $target === 'heic';
        if (! $encodeHeic && (! in_array($target, self::SUPPORTED_EXTENSIONS, true) || ! $format)) {
            throw new UploadException('配置的图片转换格式不受支持');
        }

        $outputPath = tempnam(sys_get_temp_dir(), 'lsky-image-');
        if ($outputPath === false) {
            throw new UploadException('暂时无法创建图片处理文件');
        }

        try {
            if ($streamPng) {
                unset($image);
                $this->writeLargePng($path, $outputPath);
            } else {
                if ($watermark) {
                    $this->watermark($image, $watermark);
                }
                if ($encodeHeic) {
                    $native = $image->core()->native();
                    if ($strip) {
                        $native->stripImage();
                    }
                    $native->setImageFormat('HEIC');
                    $native->setCompressionQuality($quality);
                    $native->setImageCompressionQuality($quality);
                    $native->writeImage('HEIC:'.$outputPath);
                } else {
                    $image->encodeUsingFormat($format, quality: $quality, strip: $strip)->save($outputPath);
                }
            }
        } catch (\Throwable $exception) {
            @unlink($outputPath);

            if ($exception instanceof UploadException) {
                throw $exception;
            }
            if ($exception instanceof ImageException || $exception instanceof \ImagickException) {
                report($exception);
                throw new UploadException('图片转换失败或资源不足，请降低像素、帧数或尝试 JPEG、WebP', previous: $exception);
            }

            throw $exception;
        }

        return [$target, $outputPath];
    }

    private function isSimplePng(string $path): bool
    {
        $stream = fopen($path, 'rb');
        if ($stream === false) {
            return false;
        }

        try {
            fseek($stream, 8); // The PNG signature has already been checked by inspect().
            while (strlen($header = fread($stream, 8) ?: '') === 8) {
                $chunk = unpack('Nlength/a4type', $header);
                if ($chunk['type'] === 'IDAT') {
                    return true;
                }
                if (in_array($chunk['type'], ['acTL', 'eXIf', 'IEND'], true)
                    || fseek($stream, $chunk['length'] + 4, SEEK_CUR) !== 0) {
                    return false;
                }
            }

            return false;
        } finally {
            fclose($stream);
        }
    }

    private function checkGdPngMemory(int $pixels): void
    {
        $memoryLimit = ini_parse_quantity((string) ini_get('memory_limit'));
        $available = $memoryLimit - memory_get_usage(true) - 32 * 1024 * 1024;
        // GD can hold both libpng's RGBA data and GD's pixel buffer while decoding.
        if ($memoryLimit > 0 && $pixels * 9 > $available) {
            throw new UploadException('PNG 超出当前处理内存，请降低像素或尝试 JPEG、WebP');
        }
    }

    private function writeLargePng(string $source, string $target): void
    {
        // Copy the validated compressed pixels and rendering chunks exactly, including alpha.
        $input = fopen($source, 'rb');
        if (! $input) {
            throw new UploadException('无法读取待处理的 PNG');
        }
        try {
            $output = fopen($target, 'wb');
            if (! $output) {
                throw new UploadException('PNG 无法写入，请检查服务器的临时储存');
            }
            try {
                $this->copyPngPixels($input, $output);
            } finally {
                fclose($output);
            }
        } finally {
            fclose($input);
        }
    }

    private function copyPngPixels(mixed $input, mixed $output): void
    {
        $signature = fread($input, 8);
        $renderingChunks = ['IHDR', 'PLTE', 'IDAT', 'IEND', 'tRNS', 'cHRM', 'gAMA', 'sRGB', 'sBIT', 'cICP', 'mDCV', 'cLLI'];
        if (! $signature || fwrite($output, $signature) !== 8) {
            throw new UploadException('PNG 文件不完整或临时储存不可写');
        }
        while (strlen($header = fread($input, 8) ?: '') === 8) {
            $chunk = unpack('Nlength/a4type', $header);
            $length = $chunk['length'] + 4; // Include the chunk CRC.
            if (in_array($chunk['type'], $renderingChunks, true)) {
                if (fwrite($output, $header) !== 8 || stream_copy_to_stream($input, $output, $length) !== $length) {
                    throw new UploadException('PNG 文件不完整或临时储存不可写');
                }
                if ($chunk['type'] === 'IEND') {
                    return;
                }
            } elseif (fseek($input, $length, SEEK_CUR) !== 0) {
                break;
            }
        }

        throw new UploadException('PNG 文件不完整');
    }

    public function thumbnail(mixed $stream, string $extension, Collection $configs, int $maximum = 400): EncodedImageInterface
    {
        $image = $extension === 'heic' ? $this->decodeHeic($stream) : $this->manager(false)->decodeStream($stream);
        $this->checkFrames($image);

        if ($watermark = $this->watermarkConfig($configs, Mode::Dynamic)) {
            $this->watermark($image, $watermark);
        }

        return $image->scaleDown($maximum, $maximum)->encodeUsingFormat(Format::WEBP, quality: 80, strip: true);
    }

    public function render(mixed $stream, string $extension, Collection $configs): EncodedImageInterface
    {
        $image = $extension === 'heic' ? $this->decodeHeic($stream) : $this->manager()->decodeStream($stream);
        $this->checkFrames($image);

        if ($watermark = $this->watermarkConfig($configs, Mode::Dynamic)) {
            $this->watermark($image, $watermark);
        }

        $format = match ($extension) {
            'heic' => Format::WEBP,
            'psd', 'tif', 'tiff', 'bmp' => Format::PNG,
            default => Format::create($extension),
        };

        return $image->encodeUsingFormat(
            $format,
            quality: max(1, min(100, (int) $configs->get(GroupConfigKey::ImageSaveQuality, 75))),
            strip: $extension === 'heic' || (bool) config('image.strip_metadata'),
        );
    }

    public function heicModerationImage(string $path, string $target): void
    {
        $image = $this->decodeHeic($path);
        $this->checkFrames($image);
        $image->scaleDown(1920, 1920)->encodeUsingFormat(Format::JPEG, quality: 85, strip: true)->save($target);
    }

    private function heicDecoder(): \Imagick
    {
        if (! class_exists(\Imagick::class) || ! \Imagick::queryFormats('HEIC')) {
            throw new UploadException('服务器未安装 HEIC 处理组件');
        }

        return new \Imagick();
    }

    private function decodeHeic(mixed $source): ImageInterface
    {
        $native = $this->heicDecoder();
        try {
            if (is_resource($source)) {
                $native->readImageFile($source, 'HEIC:source.heic');
            } else {
                $native->readImage('HEIC:'.$source);
            }
            if ($native->getImageFormat() !== 'HEIC' || $native->getNumberImages() !== 1) {
                throw new UploadException('目前仅支持单张 HEIC 照片');
            }

            return ImageManager::usingDriver(ImagickDriver::class, decodeAnimation: false)->decode($native);
        } catch (\ImagickException | ImageException $exception) {
            throw new UploadException('HEIC 图片无法解码，文件可能已损坏或超出处理资源限制', previous: $exception);
        } finally {
            $native->clear();
        }
    }

    public function watermarkConfig(Collection $configs, int $mode): ?Collection
    {
        $watermark = collect($configs->get(GroupConfigKey::WatermarkConfigs));

        return $configs->get(GroupConfigKey::IsEnableWatermark)
            && (int) $watermark->get('mode', Mode::Overlay) === $mode
                ? $watermark
                : null;
    }

    private function manager(bool $decodeAnimation = true): ImageManagerInterface
    {
        $driver = match (config('image.driver')) {
            'gd' => GdDriver::class,
            default => ImagickDriver::class,
        };

        return ImageManager::usingDriver($driver, decodeAnimation: $decodeAnimation, strip: (bool) config('image.strip_metadata'));
    }

    private function checkFrames(ImageInterface $image): void
    {
        $frames = max(1, $image->count());
        $maximumPixels = max(1, (int) config('image.max_pixels'));
        $height = max(1, $image->height());

        if ($frames > (int) config('image.max_frames') || $image->width() > intdiv(intdiv($maximumPixels, $height), $frames)) {
            throw new UploadException('图片或动画的解码像素超出限制');
        }
    }

    private function watermark(ImageInterface $image, Collection $settings): void
    {
        $driver = $settings->get('driver');
        $options = collect($settings->get('drivers', [])[$driver] ?? []);
        $position = $options->get(FontOption::Position, 'bottom-right');
        $offsetX = max(0, (int) $options->get(FontOption::X, 10));
        $offsetY = max(0, (int) $options->get(FontOption::Y, 10));

        if ($driver === 'image') {
            $path = $this->assetPath((string) $options->get(ImageOption::Image), ['jpeg', 'jpg', 'png', 'webp']);
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $this->inspect($path, $extension);
            $watermark = ImageManager::usingDriver($image->driver()::class, decodeAnimation: false)->decodePath($path);
            if ($angle = (int) $options->get(ImageOption::Rotate, 0)) {
                $watermark->rotate(-$angle, Color::transparent());
            }
            $width = (int) $options->get(ImageOption::Width, 0);
            $height = (int) $options->get(ImageOption::Height, 0);
            if ($width > 0 || $height > 0) {
                $watermark->scaleDown($width > 0 ? $width : null, $height > 0 ? $height : null);
            }
            $opacity = max(0, min(100, (int) $options->get(ImageOption::Opacity, 100))) / 100;
        } else {
            $watermark = $this->textWatermark($image, $options);
            $opacity = 1.0;
        }

        if ($position !== 'tiled') {
            $image->insert($watermark, $offsetX, $offsetY, $position, $opacity);

            return;
        }

        $stepX = max($watermark->width() + $offsetX, (int) ceil($image->width() / 24));
        $stepY = max($watermark->height() + $offsetY, (int) ceil($image->height() / 24));

        for ($x = 0; $x < $image->width(); $x += $stepX) {
            for ($y = 0; $y < $image->height(); $y += $stepY) {
                $image->insert($watermark, $x, $y, transparency: $opacity);
            }
        }
    }

    private function textWatermark(ImageInterface $image, Collection $options): ImageInterface
    {
        $fontPath = $this->assetPath((string) $options->get(FontOption::Font), ['ttf', 'otf']);
        $size = max(1, min(300, (int) $options->get(FontOption::Size, 50)));
        $text = mb_substr((string) $options->get(FontOption::Text, 'Lsky Pro'), 0, 100);
        $driver = $image->driver();
        $processor = $driver->fontProcessor();
        $font = (new FontFactory())
            ->filepath($fontPath)
            ->size($size)
            ->align(Alignment::LEFT, Alignment::TOP)
            ->color($options->get(FontOption::Color, '000000'))
            ->font();
        $box = $processor->boxSize($text, $font);
        $watermark = $driver->createImage(max(1, $box->width() + 8), max(1, $box->height() + 8));
        $watermark->text($text, 4, 4, $font);
        if ($angle = (int) $options->get(FontOption::Angle, 0)) {
            $watermark->rotate(-$angle, Color::transparent());
        }

        return $watermark;
    }

    private function assetPath(string $name, array $extensions): string
    {
        $root = realpath(storage_path('app/public'));
        $path = $root ? realpath($root.'/'.ltrim($name, '/')) : false;

        if (! $path || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR) || ! is_readable($path)
            || ! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $extensions, true)) {
            throw new UploadException('水印文件不可用');
        }

        return $path;
    }
}
