# Image Compression Service

A high-performance Laravel service for compressing and optimizing images using local CLI tools.

## Features

- **Local CLI Compression**: Uses `pngquant`, `mozjpeg`, `cwebp`, and `avifenc` for optimal compression
- **Format Conversion**: Convert between PNG, JPEG, WebP, and AVIF formats
- **Queue Support**: Background processing for CPU-intensive operations
- **Fallback Support**: Graceful fallback to GD extension when CLI tools unavailable
- **Comprehensive Metrics**: Track compression ratios and file size savings
- **Laravel Integration**: Fully integrated with Laravel's service container and configuration

## Installation

### 1. Install CLI Tools

#### Ubuntu/Debian

```bash
sudo apt-get update
sudo apt-get install -y pngquant mozjpeg-tools webp libavif-bin

# Create symlink for mozjpeg
sudo ln -sf /usr/bin/cjpeg /usr/local/bin/mozjpeg
```

#### macOS (Homebrew)

```bash
brew install pngquant mozjpeg webp libavif
```

#### Docker

```dockerfile
RUN apt-get update && apt-get install -y \
    pngquant \
    mozjpeg-tools \
    webp \
    libavif-bin \
    && ln -sf /usr/bin/cjpeg /usr/local/bin/mozjpeg
```

### 2. Install Composer Dependencies

```bash
composer require symfony/process
```

### 3. Publish Configuration

The service uses `config/image-compression.php`:

```php
return [
    'preset' => env('IMG_COMP_PRESET', 'default'),

    'cli' => [
        'pngquant' => null,  // Auto-discover via `which`
        'mozjpeg'  => null,
        'cwebp'    => null,
        'avifenc'  => null,
    ],

    'quality' => [
        'png'  => '65-80',   // pngquant quality range
        'jpeg' => 80,        // mozjpeg quality
        'webp' => 80,        // cwebp quality
        'avif' => 60,        // avifenc quality
    ],

    'timeout' => 60,         // CLI process timeout

    'fallback' => [
        'enabled' => true,   // Fall back to GD if CLI fails
        'log_failures' => true,
    ],
];
```

Add to your `.env`:

```
IMG_COMP_PRESET=default
```

## Usage

### Basic Compression

```php
use App\Services\ImageCompressionService;

class ImageController extends Controller
{
    public function store(Request $request, ImageCompressionService $compressor)
    {
        $uploadedFile = $request->file('photo');

        // Compress and convert to WebP
        $result = $compressor->compress($uploadedFile, 'webp');

        // Store the compressed image
        Storage::put('images/photo.webp', $result['binary']);

        return response()->json([
            'original_size' => $result['original_kb'] . 'KB',
            'compressed_size' => $result['compressed_kb'] . 'KB',
            'savings' => $result['savings_pct'] . '%',
            'format' => $result['format']
        ]);
    }
}
```

### Queue Processing

For CPU-intensive compression, use the included job:

```php
use App\Jobs\CompressImageJob;

// Dispatch to queue
CompressImageJob::dispatch(
    $sourcePath,
    'optimized/image.webp',
    'webp'
);
```

### Check Available Tools

```php
$compressor = app(ImageCompressionService::class);

// Check which tools are installed
$tools = $compressor->checkTools();
// ['pngquant' => true, 'mozjpeg' => true, 'cwebp' => true, 'avifenc' => false]

// Get available output formats
$formats = $compressor->getAvailableFormats();
// ['png', 'jpeg', 'webp']
```

### Advanced Usage

```php
// Compress keeping original format
$result = $compressor->compress('/path/to/image.jpg');

// Convert PNG to AVIF
$result = $compressor->compress('/path/to/image.png', 'avif');

// Result structure
[
    'binary' => '...',           // Compressed image data
    'original_kb' => 150.5,      // Original file size
    'compressed_kb' => 45.2,     // Compressed file size
    'savings_pct' => 70.0,       // Compression percentage
    'format' => 'webp',          // Output format
    'original_format' => 'jpeg', // Input format
    'compression_method' => 'cli' // 'cli' or 'gd_fallback'
]
```

## Integration with Existing ImageAgent

Update your `ImageAgent` to use the new compression service:

```php
use App\Services\ImageCompressionService;

class ImageAgent
{
    public static function generateImage(array $blogPost): array
    {
        // ... existing image generation code ...

        // Compress the generated image
        $compressor = app(ImageCompressionService::class);

        // Optimize main image
        $optimized = $compressor->compress($publicImagePath, 'webp');
        file_put_contents($publicImagePath, $optimized['binary']);

        // Create compressed thumbnail
        $thumbnail = $compressor->compress($publicImagePath);
        ImageProcessor::createThumbnail(
            $publicImagePath,
            $publicThumbnailPath,
            300,
            200,
            85
        );

        // Further compress thumbnail
        $thumbnailOptimized = $compressor->compress($publicThumbnailPath, 'webp');
        file_put_contents($publicThumbnailPath, $thumbnailOptimized['binary']);

        return [
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath
        ];
    }
}
```

## Testing

### Run Tests

```bash
# Unit tests (mock CLI tools)
vendor/bin/pest tests/Unit

# Integration tests (requires CLI tools installed)
vendor/bin/pest tests/Feature
```

### CI Setup

The service includes GitHub Actions workflow that:

- Installs all compression tools
- Runs comprehensive tests
- Validates actual compression ratios
- Tests on multiple PHP versions

## Performance Benchmarks

Typical compression results with default settings:

| Format                | Original | Compressed | Savings |
| --------------------- | -------- | ---------- | ------- |
| JPEG → JPEG (mozjpeg) | 1.2MB    | 800KB      | 33%     |
| PNG → PNG (pngquant)  | 2.1MB    | 950KB      | 55%     |
| JPEG → WebP           | 1.2MB    | 480KB      | 60%     |
| PNG → WebP            | 2.1MB    | 420KB      | 80%     |
| JPEG → AVIF           | 1.2MB    | 320KB      | 73%     |

## Configuration Options

### Quality Presets

Create custom presets in config:

```php
'presets' => [
    'high_quality' => [
        'png'  => '80-95',
        'jpeg' => 95,
        'webp' => 95,
        'avif' => 80,
    ],
    'web_optimized' => [
        'png'  => '60-75',
        'jpeg' => 75,
        'webp' => 75,
        'avif' => 55,
    ],
],
```

### Custom Binary Paths

```php
'cli' => [
    'pngquant' => '/usr/local/bin/pngquant',
    'mozjpeg'  => '/opt/mozjpeg/bin/cjpeg',
    'cwebp'    => '/usr/bin/cwebp',
    'avifenc'  => '/usr/local/bin/avifenc',
],
```

## Error Handling

The service provides comprehensive error handling:

- **Missing Tools**: Graceful fallback to GD extension
- **Invalid Files**: Clear error messages for unsupported formats
- **Process Timeouts**: Configurable timeouts prevent hanging
- **Memory Management**: Proper cleanup of temporary files

## Logging

All compression operations are logged with detailed metrics:

```
[INFO] Image compressed successfully
{
    "source": "photo.jpg",
    "original_kb": 150.5,
    "compressed_kb": 45.2,
    "savings_pct": 70.0,
    "format": "webp",
    "method": "cli"
}
```

## Contributing

1. Ensure all CLI tools are installed for testing
2. Run the full test suite: `vendor/bin/pest`
3. Add tests for new features
4. Update documentation for any new configuration options

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
