---
layout: default
title: CropEntropy Class
---

# CropEntropy

The `CropEntropy` class implements intelligent cropping based on image entropy (edge detection) to preserve the most visually important areas of an image.

## Class Overview

```php
class drzippie\crop\CropEntropy extends drzippie\crop\Crop
```

This class provides content-aware cropping by analyzing image entropy. It's ideal for:
- High-quality image cropping
- Preserving important visual content
- Artistic and complex images
- Cases where visual quality is paramount

## Algorithm

The CropEntropy algorithm works as follows:

1. **Convert to grayscale** - Analyze luminance information
2. **Apply edge detection** - Identify areas of high contrast
3. **Calculate entropy** - Measure "energy" or "edginess" in image regions
4. **Find optimal position** - Locate the area with highest entropy
5. **Position crop area** - Ensure high-entropy areas are preserved

This approach is:
- ✅ **Intelligent** - Considers image content
- ✅ **High quality** - Preserves important details
- ✅ **Content-aware** - Finds focal points
- ❌ **Slower** - Requires image analysis
- ❌ **Memory intensive** - Processes entire image

## What is Entropy?

In image processing, entropy refers to the amount of information or "energy" in different parts of an image:

- **High entropy areas**: Edges, textures, detailed regions, faces, text
- **Low entropy areas**: Sky, solid colors, blurred backgrounds, gradients

Examples:
- A photo of an anthill has high entropy (lots of detail)
- A photo of clear sky has low entropy (uniform color)
- A portrait has high entropy around the face and eyes

## Constructor

```php
public function __construct(string|Imagick|null $image = null)
```

Creates a new CropEntropy instance.

**Parameters:**
- `$image` - File path, Imagick object, or null

**Examples:**
```php
// From file path
$crop = new CropEntropy('detailed_photo.jpg');

// From Imagick object
$imagick = new Imagick('artwork.jpg');
$crop = new CropEntropy($imagick);

// Empty instance
$crop = new CropEntropy();
$crop->setImage(new Imagick('complex_image.jpg'));
```

## Basic Usage

### Intelligent Content Preservation

```php
use drzippie\crop\CropEntropy;

// Preserve the most important parts of the image
$crop = new CropEntropy('landscape_photo.jpg');
$result = $crop->resizeAndCrop(400, 300);
$result->writeImage('smart_crop.jpg');
```

### Product Photography

```php
use drzippie\crop\CropEntropy;

// Ideal for product images where subject might be off-center
$crop = new CropEntropy('product_photo.jpg');

// Square crop for catalog
$square = $crop->resizeAndCrop(400, 400);
$square->writeImage('product_square.jpg');

// Rectangular crop for banner
$banner = $crop->resizeAndCrop(600, 300);
$banner->writeImage('product_banner.jpg');
```

### Portrait Photography

```php
use drzippie\crop\CropEntropy;

// Automatically focus on faces and important features
$crop = new CropEntropy('portrait.jpg');
$result = $crop->resizeAndCrop(300, 400);
$result->writeImage('portrait_crop.jpg');
```

## Advanced Configuration

### High-Quality Settings

```php
use drzippie\crop\CropEntropy;

$crop = new CropEntropy('important_image.jpg');

// Use highest quality filter
$crop->setFilter(Imagick::FILTER_LANCZOS);

// Minimize blur for sharpness
$crop->setBlur(0.2);

// Enable auto-orientation
$crop->setAutoOrient(true);

$result = $crop->resizeAndCrop(500, 400);
```

### Performance vs Quality Balance

```php
use drzippie\crop\CropEntropy;

$crop = new CropEntropy('image.jpg');

// For faster processing (slight quality reduction)
$crop->setFilter(Imagick::FILTER_CUBIC)
     ->setBlur(0.5);

// For maximum quality (slower processing)
$crop->setFilter(Imagick::FILTER_LANCZOS)
     ->setBlur(0.3);

$result = $crop->resizeAndCrop(400, 300);
```

## Method Chaining

```php
use drzippie\crop\CropEntropy;

$result = (new CropEntropy('complex_image.jpg'))
    ->setFilter(Imagick::FILTER_LANCZOS)
    ->setBlur(0.3)
    ->setAutoOrient(true)
    ->resizeAndCrop(600, 400);

$result->writeImage('output.jpg');
```

## Performance Characteristics

### Speed
- **Slowest** of all cropping strategies
- Requires full image analysis
- Processing time increases with image size

### Memory Usage
- **Highest** memory footprint
- Analyzes entire image for entropy
- May require memory limit adjustments for large images

### Quality Results
- **Highest** visual quality
- Preserves important image content
- Best for critical applications

## Use Cases

### Ideal For:
- **Artwork and photography** - Preserves artistic composition
- **Complex images** - Multiple subjects or focal points
- **Editorial images** - Important content must be preserved
- **Marketing materials** - High-quality visual impact required
- **Face detection alternatives** - Finds faces through entropy
- **Architectural photos** - Preserves structural details

### Not Ideal For:
- **Simple images** - Overkill for basic content
- **Batch processing** - Too slow for large volumes
- **Low-resolution images** - May not have enough detail
- **Time-sensitive applications** - Processing overhead too high

## Performance Comparison

For a 1920x1080 image:
- **CropCenter**: ~10ms
- **CropBalanced**: ~50ms
- **CropEntropy**: ~100ms

Memory usage (approximate):
- **CropCenter**: Base image + minimal overhead
- **CropBalanced**: Base image + 4x analysis regions
- **CropEntropy**: Base image + full entropy analysis

## Error Handling

```php
use drzippie\crop\CropEntropy;

try {
    // Increase memory limit for large images
    ini_set('memory_limit', '512M');
    
    $crop = new CropEntropy('large_image.jpg');
    $result = $crop->resizeAndCrop(800, 600);
    $result->writeImage('output.jpg');
    
} catch (RuntimeException $e) {
    echo "Processing error: " . $e->getMessage();
} catch (ImagickException $e) {
    echo "ImageMagick error: " . $e->getMessage();
} finally {
    // Clean up memory
    if (isset($result)) {
        $result->clear();
        $result->destroy();
    }
}
```

## Examples

### Smart Gallery Thumbnails

```php
use drzippie\crop\CropEntropy;

function createSmartThumbnail($inputPath, $outputPath, $size = 200) {
    $crop = new CropEntropy($inputPath);
    $result = $crop
        ->setFilter(Imagick::FILTER_LANCZOS)
        ->setBlur(0.3)
        ->resizeAndCrop($size, $size);
    
    $result->writeImage($outputPath);
    return $outputPath;
}

// Usage
$thumb = createSmartThumbnail('complex_photo.jpg', 'smart_thumb.jpg', 300);
```

### Responsive Image Generation

```php
use drzippie\crop\CropEntropy;

function generateResponsiveImages($inputPath, $baseName) {
    $crop = new CropEntropy($inputPath);
    
    $sizes = [
        'mobile' => [320, 240],
        'tablet' => [768, 576],
        'desktop' => [1200, 900]
    ];
    
    $outputs = [];
    foreach ($sizes as $device => [$width, $height]) {
        $result = $crop->resizeAndCrop($width, $height);
        $outputPath = "{$baseName}_{$device}.jpg";
        $result->writeImage($outputPath);
        $outputs[$device] = $outputPath;
    }
    
    return $outputs;
}

// Usage
$responsive = generateResponsiveImages('hero_image.jpg', 'hero');
```

### Content-Aware Batch Processing

```php
use drzippie\crop\CropEntropy;

function processHighQualityBatch($inputDir, $outputDir, $width, $height) {
    $files = glob($inputDir . '/*.jpg');
    
    // Increase memory limit for entropy processing
    ini_set('memory_limit', '1G');
    
    foreach ($files as $file) {
        echo "Processing: " . basename($file) . "\n";
        
        $crop = new CropEntropy($file);
        $result = $crop
            ->setFilter(Imagick::FILTER_LANCZOS)
            ->setBlur(0.3)
            ->resizeAndCrop($width, $height);
        
        $filename = basename($file);
        $result->writeImage($outputDir . '/' . $filename);
        
        // Clean up memory
        $result->clear();
        $result->destroy();
    }
}

// Process all images with high quality
processHighQualityBatch('input/', 'output/', 500, 400);
```

## Implementation Details

The entropy calculation process:

1. **Grayscale conversion** - Reduces complexity
2. **Edge detection** - Identifies high-contrast areas
3. **Entropy calculation** - Measures information density
4. **Optimal positioning** - Finds best crop location

The algorithm analyzes image slices (horizontal and vertical) to find the position with maximum entropy.

## Performance Optimization Tips

### Memory Management
```php
// For large images
ini_set('memory_limit', '1G');

// Clean up after processing
$result->clear();
$result->destroy();
```

### Batch Processing
```php
// Process in smaller batches
$chunks = array_chunk($files, 10);
foreach ($chunks as $chunk) {
    // Process chunk
    gc_collect_cycles(); // Force garbage collection
}
```

## See Also

- [Crop](crop.html) - Base class documentation
- [CropCenter](crop-center.html) - Simple center cropping
- [CropBalanced](crop-balanced.html) - Balanced cropping
- [API Reference](index.html) - Complete API documentation
- [Advanced Usage Examples](../examples/advanced-usage.html) - More complex examples