<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ConvertMdxToBlade;
use App\Console\Commands\GenerateBlogImages;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the MDX to Blade converter command
app(ConvertMdxToBlade::class);

// Register the blog image generator command
app(GenerateBlogImages::class);
