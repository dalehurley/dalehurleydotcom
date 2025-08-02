<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ConvertMdxToBlade;
use App\Console\Commands\GenerateBlogImages;
use App\Console\Commands\RegenerateThumbnails;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the MDX to Blade converter command
app(ConvertMdxToBlade::class);

// Register the blog image generator command
app(GenerateBlogImages::class);

// Register the thumbnail regeneration command
app(RegenerateThumbnails::class);
