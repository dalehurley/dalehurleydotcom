<?php

use App\Services\ImageCompressionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Set up test configuration
    config([
        'image-compression' => [
            'preset' => 'default',
            'cli' => [
                'pngquant' => null,
                'mozjpeg'  => null,
                'cwebp'    => null,
                'avifenc'  => null,
            ],
            'quality' => [
                'png'  => '65-80',
                'jpeg' => 80,
                'webp' => 80,
                'avif' => 60,
            ],
            'timeout' => 60,
            'fallback' => [
                'enabled' => true,
                'log_failures' => true,
            ],
        ]
    ]);

    $this->service = new ImageCompressionService();

    // Create a test image
    $this->testImagePath = storage_path('app/test-image.jpg');
    createTestImage($this->testImagePath);
});

afterEach(function () {
    if (file_exists($this->testImagePath)) {
        unlink($this->testImagePath);
    }
});

it('can check available CLI tools', function () {
    $tools = $this->service->checkTools();

    expect($tools)->toBeArray()
        ->and($tools)->toHaveKeys(['pngquant', 'mozjpeg', 'cwebp', 'avifenc']);

    foreach ($tools as $tool => $available) {
        expect($available)->toBeBool();
    }
});

it('can get available formats', function () {
    $formats = $this->service->getAvailableFormats();

    expect($formats)->toBeArray();

    // At minimum should have some formats available (depending on system)
    // In CI with proper setup, should have all formats
});

it('can compress a JPEG image', function () {
    if (!in_array('jpeg', $this->service->getAvailableFormats())) {
        $this->markTestSkipped('mozjpeg not available');
    }

    $result = $this->service->compress($this->testImagePath, 'jpeg');

    expect($result)->toHaveKeys([
        'binary',
        'original_kb',
        'compressed_kb',
        'savings_pct',
        'format'
    ])
        ->and($result['binary'])->not()->toBeEmpty()
        ->and($result['format'])->toBe('jpeg')
        ->and($result['original_kb'])->toBeGreaterThan(0)
        ->and($result['compressed_kb'])->toBeGreaterThan(0);
});

it('can convert JPEG to WebP', function () {
    if (!in_array('webp', $this->service->getAvailableFormats())) {
        $this->markTestSkipped('cwebp not available');
    }

    $result = $this->service->compress($this->testImagePath, 'webp');

    expect($result)->toHaveKeys([
        'binary',
        'original_kb',
        'compressed_kb',
        'savings_pct',
        'format'
    ])
        ->and($result['binary'])->not()->toBeEmpty()
        ->and($result['format'])->toBe('webp')
        ->and($result['savings_pct'])->toBeGreaterThan(0);
});

it('falls back to GD when CLI tools fail', function () {
    // Mock the config to enable fallback
    config(['image-compression.fallback.enabled' => true]);

    // Create a service with invalid binary paths to force fallback
    $invalidConfig = config('image-compression');
    $invalidConfig['cli']['cwebp'] = '/invalid/path/cwebp';
    config(['image-compression' => $invalidConfig]);

    $service = new ImageCompressionService();
    $result = $service->compress($this->testImagePath, 'webp');

    expect($result)->toHaveKey('compression_method')
        ->and($result['compression_method'])->toBe('gd_fallback')
        ->and($result['binary'])->not()->toBeEmpty();
});

it('throws exception when format is unsupported', function () {
    // Create a file with unsupported mime type
    $textFile = storage_path('app/test.txt');
    file_put_contents($textFile, 'This is not an image');

    try {
        $this->service->compress($textFile, 'webp');
    } finally {
        unlink($textFile);
    }
})->throws(Exception::class);

it('throws exception when source file does not exist', function () {
    $this->service->compress('/nonexistent/file.jpg');
})->throws(RuntimeException::class);

it('can handle uploaded files', function () {
    $uploadedFile = UploadedFile::fake()->image('test.jpg', 100, 100);

    // Move to temporary location for testing
    $tempPath = storage_path('app/temp-upload.jpg');
    $uploadedFile->move(storage_path('app'), 'temp-upload.jpg');

    try {
        if (!empty($this->service->getAvailableFormats())) {
            $result = $this->service->compress($tempPath);

            expect($result)->toHaveKeys([
                'binary',
                'original_kb',
                'compressed_kb',
                'savings_pct',
                'format'
            ]);
        } else {
            $this->markTestSkipped('No compression tools available');
        }
    } finally {
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
});

// Helper function to create a test image
function createTestImage(string $path): void
{
    $image = imagecreatetruecolor(100, 100);
    $red = imagecolorallocate($image, 255, 0, 0);
    imagefill($image, 0, 0, $red);

    // Add some complexity to make compression meaningful
    for ($i = 0; $i < 50; $i++) {
        $x = rand(0, 99);
        $y = rand(0, 99);
        $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imagesetpixel($image, $x, $y, $color);
    }

    imagejpeg($image, $path, 100);
    imagedestroy($image);
}
