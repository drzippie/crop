---
layout: default
title: Usage Guide
---

# Usage Guide

## Basic Usage

### Simple Cropping

```php
use drzippie\crop\CropCenter;

// Create cropper and process image
$crop = new CropCenter('path/to/image.jpg');
$result = $crop->resizeAndCrop(300, 200);
$result->writeImage('path/to/output.jpg');
```

### All Cropping Strategies

```php
use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};

// Center-based cropping (fastest)
$center = new CropCenter($imagePath);
$result = $center->resizeAndCrop(300, 200);

// Entropy-based cropping (intelligent edge detection)
$entropy = new CropEntropy($imagePath);
$result = $entropy->resizeAndCrop(300, 200);

// Balanced cropping (weighted center of interest)
$balanced = new CropBalanced($imagePath);
$result = $balanced->resizeAndCrop(300, 200);
```

## Advanced Usage

### Method Chaining

```php
use drzippie\crop\CropEntropy;

$result = (new CropEntropy())
    ->setImage($imagickObject)
    ->setFilter(Imagick::FILTER_LANCZOS)
    ->setBlur(0.8)
    ->setAutoOrient(true)
    ->resizeAndCrop(300, 200);
```

### Working with Imagick Objects

```php
use drzippie\crop\CropBalanced;

// Create from existing Imagick object
$imagick = new Imagick('image.jpg');
$crop = new CropBalanced($imagick);

// Or set later
$crop = new CropBalanced();
$crop->setImage($imagick);
```

### Custom Settings

```php
use drzippie\crop\CropEntropy;

$crop = new CropEntropy($imagePath);

// Set resize filter (default: FILTER_CUBIC)
$crop->setFilter(Imagick::FILTER_LANCZOS);

// Set blur factor (default: 0.5)
$crop->setBlur(1.0);

// Enable/disable auto-orientation (default: true)
$crop->setAutoOrient(false);

$result = $crop->resizeAndCrop(300, 200);
```

## Configuration Options

### Resize Filters

Choose the appropriate filter for your needs:

```php
use drzippie\crop\CropCenter;

$crop = new CropCenter($imagePath);

// High quality, slower
$crop->setFilter(Imagick::FILTER_LANCZOS);

// Good quality, faster (default)
$crop->setFilter(Imagick::FILTER_CUBIC);

// Fast, lower quality
$crop->setFilter(Imagick::FILTER_POINT);
```

### Blur Settings

Adjust sharpness during resize:

```php
// Sharper (0.0 - 1.0)
$crop->setBlur(0.2);

// Default
$crop->setBlur(0.5);

// Softer
$crop->setBlur(1.0);
```

### Auto-Orientation

Handle image rotation based on EXIF data:

```php
// Enable auto-orientation (default)
$crop->setAutoOrient(true);

// Disable if you want to preserve original orientation
$crop->setAutoOrient(false);
```

## Performance Optimization

### Strategy Performance

| Strategy | Speed | Quality | Best For |
|----------|-------|---------|----------|
| **CropCenter** | ⚡⚡⚡ | ⭐⭐ | Thumbnails, fast processing |
| **CropBalanced** | ⚡⚡ | ⭐⭐⭐ | General purpose, balanced results |
| **CropEntropy** | ⚡ | ⭐⭐⭐⭐ | Important content preservation |

### Memory Management

```php
// For large images, increase memory limit
ini_set('memory_limit', '512M');

// Process in batches for multiple images
$images = ['img1.jpg', 'img2.jpg', 'img3.jpg'];
foreach ($images as $image) {
    $crop = new CropCenter($image);
    $result = $crop->resizeAndCrop(300, 200);
    $result->writeImage("thumb_{$image}");
    
    // Clean up
    $result->clear();
    $result->destroy();
    unset($crop);
}
```

## Error Handling

```php
use drzippie\crop\CropEntropy;

try {
    $crop = new CropEntropy('nonexistent.jpg');
    $result = $crop->resizeAndCrop(300, 200);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Check if image is set before processing
$crop = new CropEntropy();
if ($crop->setImage($imagickObject)) {
    $result = $crop->resizeAndCrop(300, 200);
} else {
    echo "Failed to set image";
}
```

## Common Patterns

### Thumbnail Generation

```php
use drzippie\crop\CropCenter;

function createThumbnail($sourcePath, $outputPath, $width, $height) {
    $crop = new CropCenter($sourcePath);
    $result = $crop->resizeAndCrop($width, $height);
    $result->writeImage($outputPath);
    return $outputPath;
}

// Usage
$thumb = createThumbnail('photo.jpg', 'thumb.jpg', 150, 150);
```

### Responsive Images

```php
use drzippie\crop\CropBalanced;

$sizes = [
    'small' => [200, 150],
    'medium' => [400, 300],
    'large' => [800, 600]
];

$crop = new CropBalanced('original.jpg');

foreach ($sizes as $name => [$width, $height]) {
    $result = $crop->resizeAndCrop($width, $height);
    $result->writeImage("image_{$name}.jpg");
}
```

### Batch Processing

```php
use drzippie\crop\CropEntropy;

function processBatch($inputDir, $outputDir, $width, $height) {
    $files = glob($inputDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);
    
    foreach ($files as $file) {
        $crop = new CropEntropy($file);
        $result = $crop->resizeAndCrop($width, $height);
        
        $filename = basename($file);
        $result->writeImage($outputDir . '/' . $filename);
    }
}
```

## Next Steps

- 🎯 [Cropping Strategies](strategies.html) - Understand different algorithms
- 💡 [Examples](examples/) - See practical examples
- 📚 [API Reference](api/) - Complete API documentation