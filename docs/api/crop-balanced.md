---
layout: default
title: CropBalanced Class
---

# CropBalanced

The `CropBalanced` class implements a balanced cropping strategy that combines the speed of center cropping with the intelligence of entropy-based analysis.

## Class Overview

```php
class drzippie\crop\CropBalanced extends drzippie\crop\Crop
```

This class provides a middle-ground approach between simple center cropping and full entropy analysis. It's ideal for:
- General-purpose image cropping
- Balanced performance and quality
- Most web applications
- Cases where good results are needed without maximum processing time

## Algorithm

The CropBalanced algorithm works as follows:

1. **Divide image into quadrants** - Split into 4 equal squares
2. **Analyze each quadrant** - Find the most energetic point per square
3. **Calculate weighted center** - Determine the mean interest point for all quadrants
4. **Position crop area** - Use the weighted center as the focal point
5. **Optimize placement** - Ensure the crop area captures the most interesting content

This approach is:
- ✅ **Balanced** - Good performance and quality
- ✅ **Reliable** - Consistent results across image types
- ✅ **Efficient** - Faster than full entropy analysis
- ✅ **Intelligent** - Considers image content distribution
- ❌ **Moderate complexity** - More processing than center crop

## How It Works

### Quadrant Analysis

The image is divided into 4 equal regions:
```
┌─────────┬─────────┐
│    1    │    2    │
│         │         │
├─────────┼─────────┤
│    3    │    4    │
│         │         │
└─────────┴─────────┘
```

Each quadrant is analyzed for:
- **Energy density** - Amount of detail and contrast
- **Interest points** - Areas likely to contain important content
- **Entropy distribution** - Information content across the region

### Weighted Center Calculation

The algorithm:
1. Finds the most energetic point in each quadrant
2. Calculates a weighted average of these points
3. Uses this as the center point for cropping
4. Ensures the crop area includes the most interesting content from multiple regions

## Constructor

```php
public function __construct(string|Imagick|null $image = null)
```

Creates a new CropBalanced instance.

**Parameters:**
- `$image` - File path, Imagick object, or null

**Examples:**
```php
// From file path
$crop = new CropBalanced('photo.jpg');

// From Imagick object
$imagick = new Imagick('landscape.jpg');
$crop = new CropBalanced($imagick);

// Empty instance
$crop = new CropBalanced();
$crop->setImage(new Imagick('image.jpg'));
```

## Basic Usage

### General Purpose Cropping

```php
use drzippie\crop\CropBalanced;

// Good balance between speed and quality
$crop = new CropBalanced('mixed_content.jpg');
$result = $crop->resizeAndCrop(400, 300);
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

### Website Thumbnails

```php
use drzippie\crop\CropBalanced;

$crop = new CropBalanced('article_image.jpg');

// Article thumbnail
$thumb = $crop->resizeAndCrop(300, 200);
$thumb->writeImage('article_thumb.jpg');

// Hero banner
$banner = $crop->resizeAndCrop(1200, 400);
$banner->writeImage('hero_banner.jpg');
```

## Advanced Configuration

### Quality Settings

```php
use drzippie\crop\CropBalanced;

$crop = new CropBalanced('image.jpg');

// High quality settings
$crop->setFilter(Imagick::FILTER_LANCZOS)
     ->setBlur(0.3)
     ->setAutoOrient(true);

// Balanced settings (default)
$crop->setFilter(Imagick::FILTER_CUBIC)
     ->setBlur(0.5);

// Fast settings
$crop->setFilter(Imagick::FILTER_POINT)
     ->setBlur(0.8);

$result = $crop->resizeAndCrop(500, 400);
```

### Performance Optimization

```php
use drzippie\crop\CropBalanced;

$crop = new CropBalanced('large_image.jpg');

// Optimize for speed
$crop->setFilter(Imagick::FILTER_CUBIC)
     ->setBlur(0.6);

// Optimize for quality
$crop->setFilter(Imagick::FILTER_LANCZOS)
     ->setBlur(0.4);

$result = $crop->resizeAndCrop(800, 600);
```

## Method Chaining

```php
use drzippie\crop\CropBalanced;

$result = (new CropBalanced('image.jpg'))
    ->setFilter(Imagick::FILTER_LANCZOS)
    ->setBlur(0.4)
    ->setAutoOrient(true)
    ->resizeAndCrop(600, 400);

$result->writeImage('output.jpg');
```

## Performance Characteristics

### Speed
- **Moderate** processing time
- Faster than CropEntropy
- Slower than CropCenter
- Good balance for most applications

### Memory Usage
- **Moderate** memory footprint
- Analyzes 4 quadrants instead of full image
- More efficient than full entropy analysis

### Quality Results
- **Good** visual quality
- Better than center crop
- Approaching entropy-based quality
- Suitable for most use cases

## Use Cases

### Ideal For:
- **Web applications** - Good balance of speed and quality
- **Content management systems** - Reliable results across content types
- **E-commerce** - Product and category images
- **Social media** - Various aspect ratios and sizes
- **Blog thumbnails** - Mixed content types
- **Gallery systems** - Consistent quality across images
- **General purpose** - Default choice for most applications

### Not Ideal For:
- **Simple images** - Center crop may be sufficient
- **Highly complex images** - Full entropy analysis may be better
- **Batch processing** - Center crop may be faster
- **Artistic images** - Entropy analysis may preserve composition better

## Performance Comparison

For a 1920x1080 image:
- **CropCenter**: ~10ms
- **CropBalanced**: ~50ms
- **CropEntropy**: ~100ms

Memory usage (approximate):
- **CropCenter**: Base image + minimal overhead
- **CropBalanced**: Base image + 4x quadrant analysis
- **CropEntropy**: Base image + full entropy analysis

## Error Handling

```php
use drzippie\crop\CropBalanced;

try {
    $crop = new CropBalanced('image.jpg');
    $result = $crop->resizeAndCrop(400, 300);
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

### Responsive Image System

```php
use drzippie\crop\CropBalanced;

function generateResponsiveSizes($inputPath, $baseName) {
    $crop = new CropBalanced($inputPath);
    
    $sizes = [
        'xs' => [480, 320],
        'sm' => [768, 512],
        'md' => [1024, 683],
        'lg' => [1200, 800],
        'xl' => [1920, 1280]
    ];
    
    $outputs = [];
    foreach ($sizes as $size => [$width, $height]) {
        $result = $crop->resizeAndCrop($width, $height);
        $outputPath = "{$baseName}_{$size}.jpg";
        $result->writeImage($outputPath);
        $outputs[$size] = $outputPath;
    }
    
    return $outputs;
}

// Usage
$responsive = generateResponsiveSizes('hero.jpg', 'hero');
```

### Content Management System Integration

```php
use drzippie\crop\CropBalanced;

class ImageProcessor {
    private $crop;
    
    public function __construct() {
        $this->crop = new CropBalanced();
    }
    
    public function processUpload($uploadedFile, $sizes) {
        $this->crop->setImage(new Imagick($uploadedFile));
        
        $results = [];
        foreach ($sizes as $name => [$width, $height]) {
            $result = $this->crop->resizeAndCrop($width, $height);
            $outputPath = "uploads/{$name}/" . basename($uploadedFile);
            $result->writeImage($outputPath);
            $results[$name] = $outputPath;
        }
        
        return $results;
    }
}

// Usage
$processor = new ImageProcessor();
$sizes = [
    'thumbnail' => [150, 150],
    'medium' => [400, 300],
    'large' => [800, 600]
];
$images = $processor->processUpload('uploaded_image.jpg', $sizes);
```

### E-commerce Product Images

```php
use drzippie\crop\CropBalanced;

function processProductImages($inputDir, $outputDir) {
    $products = glob($inputDir . '/*.jpg');
    
    $sizes = [
        'thumb' => [100, 100],      // Cart thumbnail
        'gallery' => [400, 400],    // Product gallery
        'detail' => [800, 800],     // Product detail
        'zoom' => [1200, 1200]      // Zoom view
    ];
    
    foreach ($products as $product) {
        $crop = new CropBalanced($product);
        $productName = pathinfo($product, PATHINFO_FILENAME);
        
        foreach ($sizes as $sizeName => [$width, $height]) {
            $result = $crop->resizeAndCrop($width, $height);
            $outputPath = "{$outputDir}/{$productName}_{$sizeName}.jpg";
            $result->writeImage($outputPath);
        }
    }
}

// Process all product images
processProductImages('products/', 'processed/');
```

### Social Media Post Generator

```php
use drzippie\crop\CropBalanced;

class SocialMediaCropper {
    private $platforms = [
        'facebook' => [
            'post' => [1200, 630],
            'cover' => [1640, 859],
            'profile' => [170, 170]
        ],
        'instagram' => [
            'post' => [1080, 1080],
            'story' => [1080, 1920],
            'profile' => [110, 110]
        ],
        'twitter' => [
            'post' => [1024, 512],
            'header' => [1500, 500],
            'profile' => [128, 128]
        ]
    ];
    
    public function generateForPlatform($imagePath, $platform) {
        if (!isset($this->platforms[$platform])) {
            throw new InvalidArgumentException("Unknown platform: $platform");
        }
        
        $crop = new CropBalanced($imagePath);
        $results = [];
        
        foreach ($this->platforms[$platform] as $type => [$width, $height]) {
            $result = $crop->resizeAndCrop($width, $height);
            $outputPath = "{$platform}_{$type}.jpg";
            $result->writeImage($outputPath);
            $results[$type] = $outputPath;
        }
        
        return $results;
    }
}

// Usage
$socialCropper = new SocialMediaCropper();
$facebookImages = $socialCropper->generateForPlatform('content.jpg', 'facebook');
$instagramImages = $socialCropper->generateForPlatform('content.jpg', 'instagram');
```

## Implementation Details

The balanced algorithm:

1. **Quadrant division** - Splits image into 4 equal regions
2. **Individual analysis** - Each quadrant is analyzed for entropy
3. **Point weighting** - Interest points are weighted by their energy
4. **Center calculation** - Weighted average determines optimal crop center
5. **Boundary adjustment** - Ensures crop area fits within image bounds

This provides a good balance between:
- Processing speed (faster than full entropy)
- Quality results (better than center crop)
- Memory usage (moderate requirements)
- Reliability (consistent across image types)

## When to Choose CropBalanced

Choose CropBalanced when:
- You need good quality without maximum processing time
- Working with mixed content types
- Building general-purpose applications
- Speed and quality balance is important
- You want reliable results across various images

## See Also

- [Crop](crop.html) - Base class documentation
- [CropCenter](crop-center.html) - Simple center cropping
- [CropEntropy](crop-entropy.html) - Intelligent entropy cropping
- [API Reference](index.html) - Complete API documentation
- [Basic Usage Examples](../examples/basic-usage.html) - Getting started
- [Advanced Usage Examples](../examples/advanced-usage.html) - Complex scenarios