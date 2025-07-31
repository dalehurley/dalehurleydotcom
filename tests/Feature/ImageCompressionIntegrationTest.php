<?php

use App\Jobs\CompressImageJob;
use App\Services\ImageCompressionService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Storage::fake('public');
    
    // Create a test image
    $this->testImagePath = storage_path('app/test-integration.jpg');
    $this->createTestImageForIntegration($this->testImagePath);
});

afterEach(function () {
    if (file_exists($this->testImagePath)) {
        unlink($this->testImagePath);
    }
});

it('can compress images with real CLI tools', function () {
    $service = new ImageCompressionService();
    $availableFormats = $service->getAvailableFormats();
    
    if (empty($availableFormats)) {
        $this->markTestSkipped('No CLI compression tools available');
    }
    
    // Test with the first available format
    $format = $availableFormats[0];
    $result = $service->compress($this->testImagePath, $format);
    
    expect($result['savings_pct'])->toBeGreaterThan(0)
        ->and($result['compressed_kb'])->toBeLessThan($result['original_kb'])
        ->and($result['binary'])->not()->toBeEmpty();
    
    // Verify the compressed image is valid
    $tempFile = tempnam(sys_get_temp_dir(), 'compressed_test');
    file_put_contents($tempFile, $result['binary']);
    
    $imageInfo = getimagesize($tempFile);
    expect($imageInfo)->not()->toBeFalse();
    
    unlink($tempFile);
});

it('achieves meaningful compression ratios', function () {
    $service = new ImageCompressionService();
    
    // Create a larger, more complex test image for better compression testing
    $largeImagePath = storage_path('app/large-test.jpg');
    $this->createLargeTestImage($largeImagePath);
    
    try {
        $availableFormats = $service->getAvailableFormats();
        
        if (empty($availableFormats)) {
            $this->markTestSkipped('No CLI compression tools available');
        }
        
        foreach ($availableFormats as $format) {
            $result = $service->compress($largeImagePath, $format);
            
            // Should achieve at least some compression
            expect($result['savings_pct'])->toBeGreaterThan(5)
                ->and($result['compressed_kb'])->toBeLessThan($result['original_kb']);
        }
        
    } finally {
        if (file_exists($largeImagePath)) {
            unlink($largeImagePath);
        }
    }
});

it('can queue compression jobs', function () {
    Queue::fake();
    
    CompressImageJob::dispatch(
        $this->testImagePath,
        'compressed/test.webp',
        'webp'
    );
    
    Queue::assertPushed(CompressImageJob::class, function ($job) {
        return $job->sourcePath === $this->testImagePath
            && $job->outputPath === 'compressed/test.webp'
            && $job->targetFormat === 'webp';
    });
});

it('compression job stores result correctly', function () {
    Storage::fake('public');
    
    $job = new CompressImageJob(
        $this->testImagePath,
        'compressed/test.webp',
        'webp',
        'public'
    );
    
    $service = new ImageCompressionService();
    
    // Only run if WebP is available
    if (!in_array('webp', $service->getAvailableFormats())) {
        $this->markTestSkipped('WebP compression not available');
    }
    
    $job->handle($service);
    
    expect(Storage::disk('public')->exists('compressed/test.webp'))->toBeTrue();
    
    $storedContent = Storage::disk('public')->get('compressed/test.webp');
    expect($storedContent)->not()->toBeEmpty();
});

// Helper functions
function createTestImageForIntegration(string $path): void
{
    $image = imagecreatetruecolor(200, 200);
    
    // Create a gradient background
    for ($y = 0; $y < 200; $y++) {
        for ($x = 0; $x < 200; $x++) {
            $red = (int) (255 * ($x / 200));
            $green = (int) (255 * ($y / 200));
            $blue = (int) (255 * (($x + $y) / 400));
            
            $color = imagecolorallocate($image, $red, $green, $blue);
            imagesetpixel($image, $x, $y, $color);
        }
    }
    
    imagejpeg($image, $path, 95);
    imagedestroy($image);
}

function createLargeTestImage(string $path): void
{
    $image = imagecreatetruecolor(800, 600);
    
    // Create a complex pattern that compresses well
    for ($y = 0; $y < 600; $y++) {
        for ($x = 0; $x < 800; $x++) {
            $red = (int) (128 + 127 * sin($x / 50) * cos($y / 50));
            $green = (int) (128 + 127 * sin($x / 30) * sin($y / 40));
            $blue = (int) (128 + 127 * cos($x / 70) * cos($y / 30));
            
            $color = imagecolorallocate($image, $red, $green, $blue);
            imagesetpixel($image, $x, $y, $color);
        }
    }
    
    // Add some text for additional compression opportunities
    $white = imagecolorallocate($image, 255, 255, 255);
    imagestring($image, 5, 300, 250, 'COMPRESSION TEST', $white);
    imagestring($image, 5, 320, 300, 'LARGE IMAGE', $white);
    
    imagejpeg($image, $path, 95);
    imagedestroy($image);
}
