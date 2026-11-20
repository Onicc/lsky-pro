<?php

return [
    'driver' => env('IMAGE_DRIVER', 'imagick'),
    'max_pixels' => (int) env('IMAGE_MAX_PIXELS', 40_000_000),
    'max_frames' => (int) env('IMAGE_MAX_FRAMES', 64),
    'max_upload_bytes' => (int) env('IMAGE_MAX_UPLOAD_BYTES', 50 * 1024 * 1024),
    'strip_metadata' => env('IMAGE_STRIP_METADATA', true),
    'private_root' => storage_path('app/media'),
];
