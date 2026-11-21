<?php

namespace App;

use App\Enums\ConfigKey;
use App\Models\Config;
use App\Support\SecretValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Utils
{
    public static function e(\Throwable $e, $message = '', $level = 'error')
    {
        Log::{$level}($message, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    public static function getAvatar(string $name): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">'
            .'<defs><linearGradient id="g" x1="4" y1="4" x2="44" y2="44" gradientUnits="userSpaceOnUse">'
            .'<stop stop-color="#2563eb"/><stop offset="1" stop-color="#22c1c3"/></linearGradient></defs>'
            .'<rect width="48" height="48" rx="15" fill="url(#g)"/>'
            .'<circle cx="24" cy="18" r="4.5" fill="#fff" fill-opacity=".95"/>'
            .'<path d="M15.5 32c0-5 3.8-8.5 8.5-8.5s8.5 3.5 8.5 8.5H15.5Z" fill="#fff" fill-opacity=".95"/>'
            .'<circle cx="12.5" cy="21" r="3" fill="#fff" fill-opacity=".72"/>'
            .'<path d="M7 31c.2-3.3 2.3-5.5 5.5-5.5 1.8 0 3.2.6 4.2 1.7-1.1 1.1-1.9 2.4-2.3 3.8H7Z" fill="#fff" fill-opacity=".72"/>'
            .'<circle cx="35.5" cy="21" r="3" fill="#fff" fill-opacity=".72"/>'
            .'<path d="M33.6 27.2c1-1.1 2.4-1.7 4.2-1.7 3.2 0 5.3 2.2 5.5 5.5h-7.4c-.4-1.4-1.2-2.7-2.3-3.8Z" fill="#fff" fill-opacity=".72"/>'
            .'</svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * 获取系统配置，获取全部配置时将返回
     *
     * @param  string  $name
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    public static function config(string $name = '', mixed $default = null): mixed
    {
        /** @var Collection $configs */
        $configs = Cache::rememberForever('configs', function () {
            return Config::query()->pluck('value', 'name')->transform(function ($value, $key) {
                switch ($key) {
                    case ConfigKey::IsAllowGuestUpload:
                    case ConfigKey::IsEnableGallery:
                    case ConfigKey::IsEnableApi:
                    case ConfigKey::IsEnableRegistration:
                    case ConfigKey::IsUserNeedVerify:
                        $value = (bool) $value;
                        break;
                    case ConfigKey::Mail:
                    case ConfigKey::Group:
                        $value = collect(json_decode($value, true));
                        break;
                    case ConfigKey::UserInitialCapacity:
                        $value = sprintf('%.2f', $value);
                        break;
                    default:
                }
                return $value;
            });
        });
        if ($name === ConfigKey::Mail && $configs->has($name)) {
            $mail = $configs->get($name)->toArray();
            $path = 'mailers.smtp.password';
            Arr::set($mail, $path, SecretValue::decrypt(Arr::get($mail, $path)));

            return collect($mail);
        }

        return $name === '' ? $configs : $configs->get($name, $default);
    }

    /**
     * 生成连续日期.
     * @param  string  $start  开始日期
     * @param  string  $end  结束日期
     * @param  string  $unit  day=日，month=月，year=年
     * @return array
     */
    public static function makeDateRange(string $start, string $end, string $unit = 'day'): array
    {
        $array = [];
        $format = ['day' => 'Y-m-d', 'month' => 'Y-m', 'year' => 'Y'][$unit] ?? 'Y-m-d';
        Carbon::create($start)->range($end, 1, $unit)->forEach(function (Carbon $item) use (&$array, $format) {
            $array[] = $item->format($format);
        });
        return $array;
    }

    /**
     * 转换字段单位
     *
     * @param  int|float  $size  字节b
     * @return string
     */
    public static function formatSize(int|float $size): string
    {
        if ($size <= 0) {
            return "0.00 Bytes";
        }
        $unit = ['', 'K', 'M', 'G', 'T', 'P'];
        $base = 1024;
        $i = floor(log($size, $base));
        $n = count($unit);
        if ($i >= $n) {
            $i = $n - 1;
        }

        return sprintf("%.2f", $size / pow($base, $i)).' '.$unit[$i].'B';
    }

    /**
     * 格式化数字
     *
     * @param int|string $n 数字
     * @param int $precision 精度
     * @return int|string
     */
    public static function shortenNumber(int|string $n, int $precision = 1): int|string
    {
        if ($n < 1e+3) {
            return number_format($n);
        } else if ($n < 1e+6) {
            return number_format($n / 1e+3, $precision) . 'k';
        } else if ($n < 1e+9) {
            return number_format($n / 1e+6, $precision) . 'm';
        } else if ($n < 1e+12) {
            return number_format($n / 1e+9, $precision) . 'b';
        }

        return $n;
    }

    /**
     * 递归过滤数组元素
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  int  $mode
     * @return array
     */
    public static function filter(array $array, callable $callback = null, int $mode = 0): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::filter($value, $callback, $mode);
            }
        }
        return array_filter($array, $callback, $mode);
    }

    /**
     * 格式化配置，设置默认配置以及将字符串数字转换为数字
     *
     * @param  array  $defaults  默认配置
     * @param  array  $configs  新配置
     * @return array
     */
    public static function parseConfigs(array $defaults, array $configs): array
    {
        array_walk_recursive($configs, function (&$item) {
            if (ctype_digit($item)) {
                $item += 0;
            }
            if (is_null($item)) {
                unset($item);
            }
        });
        return self::array_merge_recursive_distinct($defaults, $configs);
    }

    /**
     * @param array<int|string, mixed> $array1
     * @param array<int|string, mixed> $array2
     *
     * @return array<int|string, mixed>
     */
    private static function array_merge_recursive_distinct(array $array1, array &$array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]) && ! array_is_list($value)) {
                $merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
