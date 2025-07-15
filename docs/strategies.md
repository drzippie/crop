---
layout: default
title: Cropping Strategies
---

# Cropping Strategies

The Crop library provides three intelligent cropping algorithms, each designed for different use cases and quality requirements.

## CropCenter

**Simple center-based cropping - Fast and reliable**

### How it works

CropCenter uses the most basic cropping technique:

1. Find the exact center of the image
2. Trim any edges that exceed the target width and height
3. Ensure the crop is perfectly centered

### When to use

- ✅ **Thumbnails** - Fast generation of uniform thumbnails
- ✅ **Batch processing** - When speed is more important than composition
- ✅ **Portrait photos** - Works well with centered subjects
- ✅ **Square crops** - Ideal for profile pictures and avatars

### Example

```php
use drzippie\crop\CropCenter;

$crop = new CropCenter('portrait.jpg');
$result = $crop->resizeAndCrop(200, 200);
$result->writeImage('profile.jpg');
```

### Performance
- **Speed**: ⚡⚡⚡ (Fastest)
- **Quality**: ⭐⭐ (Good for centered content)
- **Memory**: Low usage

---

## CropEntropy

**Edge-detection based cropping - Intelligent content preservation**

### How it works

CropEntropy finds the position with the most "energy" or "entropy" in the image:

1. Convert image to grayscale
2. Apply edge detection filter to highlight edges
3. Find the area with highest entropy (most edges/details)
4. Position crop to preserve this high-energy area

### When to use

- ✅ **Artwork and graphics** - Preserves important visual elements
- ✅ **Product photos** - Keeps focus on the product
- ✅ **Landscapes** - Maintains scenic focal points
- ✅ **Complex compositions** - Where important details might be off-center

### Example

```php
use drzippie\crop\CropEntropy;

$crop = new CropEntropy('landscape.jpg');
$result = $crop->resizeAndCrop(400, 300);
$result->writeImage('cropped_landscape.jpg');
```

### Technical Details

The entropy calculation uses:
- **Edge detection**: Emphasizes areas with high contrast
- **Grayscale conversion**: Focuses on structure over color
- **Sliding window**: Finds optimal crop position
- **Threshold filtering**: Removes noise and irrelevant details

### Performance
- **Speed**: ⚡ (Slower due to analysis)
- **Quality**: ⭐⭐⭐⭐ (Excellent content preservation)
- **Memory**: Higher usage for analysis

---

## CropBalanced

**Weighted center of interest - Balanced composition**

### How it works

CropBalanced is a refined version of CropEntropy that provides more balanced results:

1. Divide image into four equal quadrants
2. Find the most energetic point in each quadrant
3. Calculate weighted mean interest point across all quadrants
4. Position crop to balance composition

### When to use

- ✅ **General purpose** - Good balance of speed and quality
- ✅ **Mixed content** - Works well with various image types
- ✅ **Social media** - Balanced crops for sharing
- ✅ **Content management** - Reliable results for diverse imagery

### Example

```php
use drzippie\crop\CropBalanced;

$crop = new CropBalanced('mixed_content.jpg');
$result = $crop->resizeAndCrop(300, 200);
$result->writeImage('balanced_crop.jpg');
```

### Technical Details

The balanced approach:
- **Quadrant analysis**: Ensures no single area dominates
- **Weighted averaging**: Balances multiple points of interest
- **Composition rules**: Follows basic photography principles
- **Fallback handling**: Gracefully handles edge cases

### Performance
- **Speed**: ⚡⚡ (Good balance)
- **Quality**: ⭐⭐⭐ (Reliable results)
- **Memory**: Moderate usage

---

## Strategy Comparison

### Visual Comparison

| Image Type | CropCenter | CropEntropy | CropBalanced |
|------------|------------|-------------|--------------|
| **Portraits** | ✅ Good | ⚠️ May cut faces | ✅ Good |
| **Landscapes** | ⚠️ May miss focal points | ✅ Excellent | ✅ Good |
| **Products** | ⚠️ May cut product | ✅ Excellent | ✅ Good |
| **Graphics** | ❌ Poor | ✅ Excellent | ✅ Good |
| **Mixed Content** | ⚠️ Variable | ✅ Good | ✅ Excellent |

### Performance Metrics

| Strategy | Processing Time | Memory Usage | Quality Score |
|----------|----------------|--------------|---------------|
| **CropCenter** | ~10ms | Low | 7/10 |
| **CropBalanced** | ~50ms | Medium | 8/10 |
| **CropEntropy** | ~100ms | High | 9/10 |

*Times are approximate for a 1920x1080 image*

## Algorithm Details

### CropCenter Algorithm

```php
function getCenterOffset($image, $targetWidth, $targetHeight) {
    $geometry = $image->getImageGeometry();
    $x = max(0, ($geometry['width'] - $targetWidth) / 2);
    $y = max(0, ($geometry['height'] - $targetHeight) / 2);
    return ['x' => $x, 'y' => $y];
}
```

### CropEntropy Algorithm

```php
function getEntropyOffsets($image, $targetWidth, $targetHeight) {
    // 1. Enhance edges
    $measureImage = clone($image);
    $measureImage->edgeimage(1);
    
    // 2. Convert to grayscale
    $measureImage->modulateImage(100, 0, 100);
    
    // 3. Apply threshold
    $measureImage->blackThresholdImage("#070707");
    
    // 4. Find optimal position
    return $this->getOffsetFromEntropy($measureImage, $targetWidth, $targetHeight);
}
```

### CropBalanced Algorithm

```php
function getOffsetBalanced($targetWidth, $targetHeight) {
    // 1. Get entropy-based position
    $entropyOffset = $this->getEntropyOffsets($this->originalImage, $targetWidth, $targetHeight);
    
    // 2. Get random edge position for variation
    $randomOffset = $this->getRandomEdgeOffset($this->originalImage, $targetWidth, $targetHeight);
    
    // 3. Find highest energy point
    $energyPoint = $this->getHighestEnergyPoint($this->originalImage);
    
    // 4. Balance all factors
    return $this->balanceOffsets($entropyOffset, $randomOffset, $energyPoint, $targetWidth, $targetHeight);
}
```

## Choosing the Right Strategy

### Decision Matrix

**For thumbnails and avatars:**
```php
$crop = new CropCenter($image); // Fast, consistent
```

**For product catalogs:**
```php
$crop = new CropEntropy($image); // Preserves product details
```

**For content management systems:**
```php
$crop = new CropBalanced($image); // Reliable for diverse content
```

**For batch processing:**
```php
// Start with CropCenter for speed
$crop = new CropCenter($image);
// Fall back to CropBalanced for important images
```

### Custom Strategy Selection

```php
function chooseCropStrategy($imagePath, $priority = 'balanced') {
    switch ($priority) {
        case 'speed':
            return new CropCenter($imagePath);
        case 'quality':
            return new CropEntropy($imagePath);
        case 'balanced':
        default:
            return new CropBalanced($imagePath);
    }
}
```

## Next Steps

- 💡 [Examples](examples/) - See practical implementations
- 📚 [API Reference](api/) - Complete method documentation
- 🏠 [Home](index.html) - Back to overview