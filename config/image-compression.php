<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default encoder
    |--------------------------------------------------------------------------
    | Allows future extension (e.g. different quality presets)
    */
    'preset' => env('IMG_COMP_PRESET', 'default'),

    /*
    |--------------------------------------------------------------------------
    | CLI binary paths (null => discovered via `which`)
    |--------------------------------------------------------------------------
    */
    'cli' => [
        'pngquant' => env('IMG_COMP_PNGQUANT_PATH'),
        'mozjpeg'  => env('IMG_COMP_MOZJPEG_PATH'),
        'cwebp'    => env('IMG_COMP_CWEBP_PATH'),
        'avifenc'  => env('IMG_COMP_AVIFENC_PATH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Quality settings per encoder
    |--------------------------------------------------------------------------
    */
    'quality' => [
        'png'  => '65-80',   // pngquant --quality param
        'jpeg' => 80,        // mozjpeg -quality
        'webp' => 80,        // cwebp -q
        'avif' => 60,        // avifenc --min / --max
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout settings for CLI processes (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => 60,

    /*
    |--------------------------------------------------------------------------
    | Fallback settings
    |--------------------------------------------------------------------------
    */
    'fallback' => [
        'enabled' => true,  // Fall back to GD if CLI tools fail
        'log_failures' => true,
    ],
];
