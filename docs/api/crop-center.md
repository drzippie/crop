---
layout: default
title: CropCenter Class
---

# CropCenter

The `CropCenter` class implements the simplest and fastest cropping strategy by centering the crop area on the image.

## Class Overview

```php
class drzippie\crop\CropCenter extends drzippie\crop\Crop
```

This class provides basic center-based cropping functionality. It's the fastest option and ideal for:
- Thumbnail generation
- Batch processing
- Cases where simple center cropping is sufficient

## Algorithm

The CropCenter algorithm works as follows:

1. **Find the center** of the original image
2. **Calculate crop area** centered on the image center
3. **Trim edges** that exceed the target dimensions
4. **Resize** to final dimensions

This approach is:
- ✅ **Fast** - Minimal computation required
- ✅ **Predictable** - Always crops from center
- ✅ **Memory efficient** - No image analysis needed
- ❌ **Basic** - Doesn't consider image content

## Constructor

```php
public function __construct(string|Imagick|null $image = null)
```

Creates a new CropCenter instance.

**Parameters:**
- `$image` - File path, Imagick object, or null

**Examples:**
```php
// From file path
$crop = new CropCenter('photo.jpg');

// From Imagick object
$imagick = new Imagick('photo.jpg');
$crop = new CropCenter($imagick);

// Empty instance
$crop = new CropCenter();
$crop->setImage(new Imagick('photo.jpg'));
```

## Basic Usage

### Simple Center Crop

```php
use drzippie\crop\CropCenter;

$crop = new CropCenter('landscape.jpg');
$result = $crop->resizeAndCrop(300, 200);
$result->writeImage('thumbnail.jpg');
```

### Multiple Sizes

```php
use drzippie\crop\CropCenter;

$crop = new CropCenter('photo.jpg');

// Generate different sizes
$sizes = [
    'thumb' => [100, 100],
    'small' => [200, 150],
    'medium' => [400, 300],
    'large' => [800, 600]
];

foreach ($sizes as $name => [$width, $height]) {
    $result = $crop->resizeAndCrop($width, $height);
    $result->writeImage("output_{$name}.jpg");
}
```

## Advanced Configuration

### Custom Filter Settings

```php
use drzippie\crop\CropCenter;

$crop = new CropCenter('image.jpg');

// High quality (slower)
$crop->setFilter(Imagick::FILTER_LANCZOS);

// Default quality
$crop->setFilter(Imagick::FILTER_CUBIC);

// Fast processing
$crop->setFilter(Imagick::FILTER_POINT);

$result = $crop->resizeAndCrop(300, 200);
```

### Blur Adjustment

```php
use drzippie\crop\CropCenter;

$crop = new CropCenter('image.jpg');

// Sharper result
$crop->setBlur(0.2);

// Softer result
$crop->setBlur(1.0);

$result = $crop->resizeAndCrop(300, 200);
```

### Auto-Orientation

```php
use drzippie\crop\CropCenter;

$crop = new CropCenter('photo_with_exif.jpg');

// Enable auto-orientation (default)
$crop->setAutoOrient(true);

// Disable to preserve original orientation
$crop->setAutoOrient(false);

$result = $crop->resizeAndCrop(300, 200);
```

## Method Chaining

```php
use drzippie\crop\CropCenter;

$result = (new CropCenter('image.jpg'))
    ->setFilter(Imagick::FILTER_LANCZOS)
    ->setBlur(0.8)
    ->setAutoOrient(true)
    ->resizeAndCrop(400, 300);

$result->writeImage('output.jpg');
```

## Performance Characteristics

### Speed
- **Fastest** of all cropping strategies
- Minimal computation overhead
- Suitable for batch processing

### Memory Usage
- **Lowest** memory footprint
- No image analysis required
- Efficient for large images

### Quality Trade-offs
- Simple but effective for many use cases
- May not preserve important image content
- Best for images where center contains main subject

## Use Cases

### Ideal For:
- **Profile pictures** - Faces often centered
- **Product thumbnails** - Products usually centered
- **Batch processing** - Speed is priority
- **Simple galleries** - Basic thumbnail generation
- **Avatar generation** - Quick square crops

### Not Ideal For:
- **Landscape photos** - Important content may be off-center
- **Artistic images** - May crop out focal points
- **Complex compositions** - Content-aware cropping preferred

## Performance Comparison

For a 1920x1080 image:
- **CropCenter**: ~10ms
- **CropBalanced**: ~50ms
- **CropEntropy**: ~100ms

## Error Handling

```php
use drzippie\crop\CropCenter;

try {
    $crop = new CropCenter('image.jpg');
    $result = $crop->resizeAndCrop(300, 200);
    $result->writeImage('output.jpg');
} catch (RuntimeException $e) {
    echo "Error: " . $e->getMessage();
} catch (ImagickException $e) {
    echo "ImageMagick error: " . $e->getMessage();
}
```

## Examples

### Thumbnail Generation

```php
use drzippie\crop\CropCenter;

function generateThumbnail($inputPath, $outputPath, $size = 150) {
    $crop = new CropCenter($inputPath);
    $result = $crop->resizeAndCrop($size, $size);
    $result->writeImage($outputPath);
    return $outputPath;
}

// Usage
$thumb = generateThumbnail('photo.jpg', 'thumb.jpg', 200);
```

### Batch Processing

```php
use drzippie\crop\CropCenter;

function processBatch($inputDir, $outputDir, $width, $height) {
    $files = glob($inputDir . '/*.jpg');
    
    foreach ($files as $file) {
        $crop = new CropCenter($file);
        $result = $crop->resizeAndCrop($width, $height);
        
        $filename = basename($file);
        $result->writeImage($outputDir . '/' . $filename);
    }
}

// Process all images in directory
processBatch('input/', 'output/', 300, 200);
```

## Implementation Details

The `getSpecialOffset()` method simply returns the center coordinates:

```php
protected function getSpecialOffset(
    Imagick $original, 
    int $targetWidth, 
    int $targetHeight
): array {
    $geometry = $original->getImageGeometry();
    
    return [
        ($geometry['width'] - $targetWidth) / 2,
        ($geometry['height'] - $targetHeight) / 2
    ];
}
```

## See Also

- [Crop](crop.html) - Base class documentation
- [CropEntropy](crop-entropy.html) - Intelligent cropping
- [CropBalanced](crop-balanced.html) - Balanced cropping
- [API Reference](index.html) - Complete API documentation
- [Basic Usage Examples](../examples/basic-usage.html) - More examples