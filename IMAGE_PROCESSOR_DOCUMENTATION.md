# Image Processing Service

The `ImageProcessor` class provides comprehensive image optimization and manipulation capabilities for your Laravel application.

## Features

- **Image Compression & Optimization**: Reduce file sizes while maintaining quality
- **Thumbnail Generation**: Create thumbnails with aspect ratio preservation
- **Responsive Images**: Generate multiple sizes for different screen resolutions
- **WebP Conversion**: Convert images to the modern WebP format
- **Memory Management**: Proper cleanup to prevent memory leaks
- **Detailed Logging**: Track optimization results and compression ratios

## Usage Examples

### Basic Thumbnail Creation

```php
use App\Services\ImageProcessor;

// Create a 300x200 thumbnail
ImageProcessor::createThumbnail(
    '/path/to/source/image.jpg',
    '/path/to/thumbnail.webp',
    300,
    200,
    85  // Quality (0-100)
);
```

### Image Optimization

```php
// Optimize an image with size constraints
$result = ImageProcessor::optimizeImage(
    '/path/to/large-image.jpg',
    '/path/to/optimized-image.webp',
    85,    // Quality
    1920,  // Max width
    1080   // Max height
);

// Returns optimization stats:
// [
//     'original_size' => 2048576,
//     'optimized_size' => 524288,
//     'compression_ratio' => 74.39,
//     'original_dimensions' => [3840, 2160],
//     'new_dimensions' => [1920, 1080]
// ]
```

### Responsive Images

```php
// Create multiple sizes for responsive design
$sizes = [
    ['width' => 320, 'height' => 240, 'suffix' => 'small'],
    ['width' => 768, 'height' => 576, 'suffix' => 'medium'],
    ['width' => 1200, 'height' => 900, 'suffix' => 'large']
];

$images = ImageProcessor::createResponsiveImages(
    '/path/to/source.jpg',
    '/path/to/responsive/image',  // Base path without extension
    $sizes,
    85  // Quality
);

// Returns:
// [
//     'small' => '/path/to/responsive/image-small.webp',
//     'medium' => '/path/to/responsive/image-medium.webp',
//     'large' => '/path/to/responsive/image-large.webp'
// ]
```

### WebP Conversion

```php
// Convert any supported image to WebP
ImageProcessor::convertToWebP(
    '/path/to/source.png',
    '/path/to/output.webp',
    90  // Quality
);
```

### Predefined Settings

```php
// Get optimized settings for different use cases
$thumbnailSettings = ImageProcessor::getOptimizedSettings('thumbnail');
$heroSettings = ImageProcessor::getOptimizedSettings('hero');
$gallerySettings = ImageProcessor::getOptimizedSettings('gallery');
$iconSettings = ImageProcessor::getOptimizedSettings('icon');
```

## Integration with ImageAgent

The `ImageAgent` now uses `ImageProcessor` for all image operations:

1. **Main Image Optimization**: Automatically optimizes generated images to 1920x1080 max
2. **Thumbnail Creation**: Creates 300x200 thumbnails with 85% quality
3. **Error Handling**: Graceful fallback if optimization fails

## Supported Formats

- **Input**: JPEG, PNG, WebP, GIF
- **Output**: WebP (recommended for best compression)

## Performance Benefits

- **File Size Reduction**: Typically 60-80% smaller files
- **WebP Format**: Superior compression compared to JPEG/PNG
- **Responsive Images**: Serve appropriate sizes for different devices
- **Memory Efficient**: Proper resource cleanup prevents memory leaks

## Error Handling

All methods throw descriptive exceptions on failure:

```php
try {
    ImageProcessor::optimizeImage($source, $output);
} catch (\Exception $e) {
    Log::error("Image optimization failed: " . $e->getMessage());
}
```

## Logging

The service logs optimization results including:

- Original and optimized file sizes
- Compression ratios
- Dimension changes
- Processing time

Check your Laravel logs for detailed optimization reports.
