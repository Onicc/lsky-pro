<?php

namespace App\Services;

use App\Enums\GroupConfigKey;
use App\Enums\Watermark\FontOption;
use App\Enums\Watermark\ImageOption;
use App\Enums\Watermark\Mode;
use App\Exceptions\UploadException;
use Illuminate\Support\Collection;
use Intervention\Image\Alignment;
use Intervention\Image\Color;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Exceptions\RuntimeException;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Interfaces\ImageManagerInterface;
use Intervention\Image\Typography\FontFactory;

class ImageProcessor
{
    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp', 'ico', 'psd', 'webp'];

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
        $info = @getimagesize($path);

        if (! $info || ! in_array($info[2], self::IMAGE_TYPES[$extension] ?? [], true)) {
            throw new UploadException('图片内容与文件格式不一致或已损坏');
        }

        [$width, $height] = $info;
        $maximumPixels = max(1, (int) config('image.max_pixels'));

        if ($width < 1 || $height < 1 || $width > intdiv($maximumPixels, $height)) {
            throw new UploadException('图片像素数量超出限制');
        }

        return [$width, $height, $info['mime']];
    }

    public function prepare(string $path, string $extension, Collection $configs): array
    {
        $this->inspect($path, $extension);

        try {
            $image = $this->manager()->decodePath($path);
        } catch (RuntimeException $exception) {
            throw new UploadException('图片无法解码或当前服务不支持该格式', previous: $exception);
        }

        $this->checkFrames($image);
        $watermark = $this->watermarkConfig($configs, Mode::Overlay);
        $quality = max(1, min(100, (int) $configs->get(GroupConfigKey::ImageSaveQuality, 75)));
        $target = strtolower((string) $configs->get(GroupConfigKey::ImageSaveFormat));
        $preserveFormat = in_array($extension, ['gif', 'ico', 'psd'], true);
        $strip = (bool) config('image.strip_metadata');

        if ($preserveFormat || (! $watermark && $quality === 100 && $target === '' && ! $strip)) {
            return [$extension, null];
        }

        $target = $target ?: $extension;

        if (! in_array($target, self::SUPPORTED_EXTENSIONS, true) || ! ($format = Format::tryCreate($target))) {
            throw new UploadException('配置的图片转换格式不受支持');
        }

        if ($watermark) {
            $this->watermark($image, $watermark);
        }

        return [$target, $image->encodeUsingFormat($format, quality: $quality, strip: $strip)];
    }

    public function thumbnail(mixed $stream, Collection $configs, int $maximum = 400): EncodedImageInterface
    {
        $image = $this->manager(false)->decodeStream($stream);
        $this->checkFrames($image);

        if ($watermark = $this->watermarkConfig($configs, Mode::Dynamic)) {
            $this->watermark($image, $watermark);
        }

        return $image->scaleDown($maximum, $maximum)->encodeUsingFormat(Format::WEBP, quality: 80, strip: true);
    }

    public function render(mixed $stream, string $extension, Collection $configs): EncodedImageInterface
    {
        $image = $this->manager()->decodeStream($stream);
        $this->checkFrames($image);

        if ($watermark = $this->watermarkConfig($configs, Mode::Dynamic)) {
            $this->watermark($image, $watermark);
        }

        $format = in_array($extension, ['psd', 'tif', 'tiff', 'bmp'], true)
            ? Format::PNG
            : Format::create($extension);

        return $image->encodeUsingFormat(
            $format,
            quality: max(1, min(100, (int) $configs->get(GroupConfigKey::ImageSaveQuality, 75))),
            strip: (bool) config('image.strip_metadata'),
        );
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
            $watermark = $this->manager(false)->decodePath($path);
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
        $manager = $this->manager(false);
        $processor = $image->driver()->fontProcessor();
        $font = (new FontFactory())
            ->filepath($fontPath)
            ->size($size)
            ->align(Alignment::LEFT, Alignment::TOP)
            ->color($options->get(FontOption::Color, '000000'))
            ->font();
        $box = $processor->boxSize($text, $font);
        $watermark = $manager->createImage(max(1, $box->width() + 8), max(1, $box->height() + 8));
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
