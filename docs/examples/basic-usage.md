---
layout: default
title: Basic Usage Examples
---

# Basic Usage Examples

Simple examples to get you started with the Crop library.

## Getting Started

### Installation

```bash
composer require drzippie/crop
```

### Basic Setup

```php
<?php
require_once 'vendor/autoload.php';

use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};
```

## CropCenter Examples

### Simple Center Crop

```php
use drzippie\crop\CropCenter;

// Create square thumbnail
$crop = new CropCenter('photo.jpg');
$result = $crop->resizeAndCrop(200, 200);
$result->writeImage('thumbnail.jpg');
```

### Working with Different Sizes

```php
use drzippie\crop\CropCenter;

$crop = new CropCenter('landscape.jpg');

// Create various sizes
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

## CropEntropy Examples

### Intelligent Content Preservation

```php
use drzippie\crop\CropEntropy;

// Preserve the most important parts of the image
$crop = new CropEntropy('detailed_artwork.jpg');
$result = $crop->resizeAndCrop(300, 200);
$result->writeImage('cropped_artwork.jpg');
```

### Product Photography

```php
use drzippie\crop\CropEntropy;

// Ideal for product images where the subject might be off-center
$crop = new CropEntropy('product_photo.jpg');

// Square crop for catalog
$square = $crop->resizeAndCrop(400, 400);
$square->writeImage('product_square.jpg');

// Rectangular crop for banner
$banner = $crop->resizeAndCrop(600, 300);
$banner->writeImage('product_banner.jpg');
```

## CropBalanced Examples

### General Purpose Cropping

```php
use drzippie\crop\CropBalanced;

// Good balance between speed and quality
$crop = new CropBalanced('mixed_content.jpg');
$result = $crop->resizeAndCrop(350, 250);
$result->writeImage('balanced_crop.jpg');
```

### Social Media Optimization

```php
use drzippie\crop\CropBalanced;

$crop = new CropBalanced('social_post.jpg');

// Facebook cover
$facebook = $crop->resizeAndCrop(1200, 630);
$facebook->writeImage('facebook_cover.jpg');

// Instagram post
$instagram = $crop->resizeAndCrop(1080, 1080);
$instagram->writeImage('instagram_post.jpg');

// Twitter header
$twitter = $crop->resizeAndCrop(1024, 512);
$twitter->writeImage('twitter_header.jpg');
```

## Working with Imagick Objects

### From Existing Imagick Object

```php
use drzippie\crop\CropCenter;

// If you already have an Imagick object
$imagick = new Imagick('image.jpg');
$crop = new CropCenter($imagick);
$result = $crop->resizeAndCrop(200, 200);
```

### Setting Image Later

```php
use drzippie\crop\CropEntropy;

$crop = new CropEntropy();
$crop->setImage(new Imagick('image.jpg'));
$result = $crop->resizeAndCrop(300, 200);
```

## Basic Configuration

### Setting Resize Filter

```php
use drzippie\crop\CropBalanced;

$crop = new CropBalanced('image.jpg');

// High quality (slower)
$crop->setFilter(Imagick::FILTER_LANCZOS);

// Default quality (good balance)
$crop->setFilter(Imagick::FILTER_CUBIC);

// Fast processing (lower quality)
$crop->setFilter(Imagick::FILTER_POINT);

$result = $crop->resizeAndCrop(300, 200);
```

### Adjusting Blur

```php
use drzippie\crop\CropEntropy;

$crop = new CropEntropy('image.jpg');

// Sharper result
$crop->setBlur(0.2);

// Default
$crop->setBlur(0.5);

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

// Disable if you want to preserve original orientation
$crop->setAutoOrient(false);

$result = $crop->resizeAndCrop(300, 200);
```

## Method Chaining

### Fluent Interface

```php
use drzippie\crop\CropBalanced;

$result = (new CropBalanced('image.jpg'))
    ->setFilter(Imagick::FILTER_LANCZOS)
    ->setBlur(0.8)
    ->setAutoOrient(true)
    ->resizeAndCrop(400, 300);

$result->writeImage('output.jpg');
```

## Error Handling

### Basic Error Handling

```php
use drzippie\crop\CropCenter;

try {
    $crop = new CropCenter('nonexistent.jpg');
    $result = $crop->resizeAndCrop(200, 200);
    $result->writeImage('output.jpg');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Checking File Existence

```php
use drzippie\crop\CropEntropy;

$imagePath = 'photo.jpg';

if (file_exists($imagePath)) {
    $crop = new CropEntropy($imagePath);
    $result = $crop->resizeAndCrop(300, 200);
    $result->writeImage('output.jpg');
} else {
    echo "Image file not found: $imagePath";
}
```

## Memory Management

### For Large Images

```php
use drzippie\crop\CropCenter;

// Increase memory limit for large images
ini_set('memory_limit', '512M');

$crop = new CropCenter('large_image.jpg');
$result = $crop->resizeAndCrop(800, 600);
$result->writeImage('large_output.jpg');

// Clean up
$result->clear();
$result->destroy();
```

## Performance Tips

### Choosing the Right Strategy

```php
// For speed (thumbnails, batch processing)
$crop = new CropCenter('image.jpg');

// For quality (important images, artwork)
$crop = new CropEntropy('image.jpg');

// For balance (general purpose)
$crop = new CropBalanced('image.jpg');
```

### Reusing Crop Objects

```php
use drzippie\crop\CropCenter;

// Process multiple sizes from same image
$crop = new CropCenter('image.jpg');

$thumb = $crop->resizeAndCrop(100, 100);
$thumb->writeImage('thumb.jpg');

$medium = $crop->resizeAndCrop(300, 200);
$medium->writeImage('medium.jpg');

$large = $crop->resizeAndCrop(600, 400);
$large->writeImage('large.jpg');
```

## Next Steps

- 📖 [Advanced Usage](advanced-usage.html) - More complex examples
- 🎯 [Cropping Strategies](../strategies.html) - Understanding the algorithms
- 📚 [API Reference](../api/) - Complete method documentation