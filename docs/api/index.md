---
layout: default
title: API Reference
---

# API Reference

Complete API documentation for the Crop library.

## Classes

### [Crop](crop.html) (Abstract Base Class)
The foundation class that provides common functionality for all cropping strategies.

### [CropCenter](crop-center.html)
Simple center-based cropping implementation.

### [CropEntropy](crop-entropy.html)
Entropy-based cropping that preserves high-energy areas.

### [CropBalanced](crop-balanced.html)
Balanced cropping with weighted center of interest.

## Quick Reference

### Common Methods

All cropping classes inherit these methods from the base `Crop` class:

```php
// Constructor
public function __construct(string|Imagick|null $image = null)

// Main cropping method
public function resizeAndCrop(int $targetWidth, int $targetHeight): Imagick

// Configuration methods
public function setImage(Imagick $image): self
public function setFilter(int $filter): self
public function setBlur(float $blur): self
public function setAutoOrient(bool $autoOrient): self

// Getters
public function getFilter(): int
public function getBlur(): float
public function getAutoOrient(): bool

// Profiling utilities
public static function start(): void
public static function mark(): string
```

### Strategy-Specific Methods

Each cropping strategy implements:

```php
// Protected method that defines the cropping logic
protected function getSpecialOffset(Imagick $original, int $targetWidth, int $targetHeight): array
```

## Method Details

### Constructor

```php
public function __construct(string|Imagick|null $image = null)
```

Creates a new cropping instance.

**Parameters:**
- `$image` - Can be:
  - `string`: Path to image file
  - `Imagick`: Existing Imagick object
  - `null`: Create empty instance (must call `setImage()` later)

**Examples:**
```php
// From file path
$crop = new CropCenter('image.jpg');

// From Imagick object
$imagick = new Imagick('image.jpg');
$crop = new CropCenter($imagick);

// Empty instance
$crop = new CropCenter();
$crop->setImage($imagick);
```

### resizeAndCrop()

```php
public function resizeAndCrop(int $targetWidth, int $targetHeight): Imagick
```

Resizes and crops the image to the specified dimensions.

**Parameters:**
- `$targetWidth` - Target width in pixels
- `$targetHeight` - Target height in pixels

**Returns:** `Imagick` - The cropped image

**Throws:** `RuntimeException` if no image is set

**Example:**
```php
$crop = new CropEntropy('image.jpg');
$result = $crop->resizeAndCrop(300, 200);
$result->writeImage('output.jpg');
```

### setImage()

```php
public function setImage(Imagick $image): self
```

Sets the image to be processed.

**Parameters:**
- `$image` - Imagick object to process

**Returns:** `self` for method chaining

**Example:**
```php
$crop = new CropBalanced();
$crop->setImage(new Imagick('image.jpg'));
```

### setFilter()

```php
public function setFilter(int $filter): self
```

Sets the resize filter to use.

**Parameters:**
- `$filter` - Imagick filter constant

**Returns:** `self` for method chaining

**Common Filters:**
- `Imagick::FILTER_LANCZOS` - High quality, slower
- `Imagick::FILTER_CUBIC` - Good quality, default
- `Imagick::FILTER_POINT` - Fast, lower quality

**Example:**
```php
$crop = new CropCenter('image.jpg');
$crop->setFilter(Imagick::FILTER_LANCZOS);
```

### setBlur()

```php
public function setBlur(float $blur): self
```

Sets the blur factor for resizing.

**Parameters:**
- `$blur` - Blur factor (0.0 to 1.0+)

**Returns:** `self` for method chaining

**Guidelines:**
- `0.0 - 0.3`: Sharper
- `0.5`: Default
- `0.8 - 1.0`: Softer

**Example:**
```php
$crop = new CropEntropy('image.jpg');
$crop->setBlur(0.3); // Sharper result
```

### setAutoOrient()

```php
public function setAutoOrient(bool $autoOrient): self
```

Enables or disables automatic orientation based on EXIF data.

**Parameters:**
- `$autoOrient` - True to enable, false to disable

**Returns:** `self` for method chaining

**Example:**
```php
$crop = new CropBalanced('image.jpg');
$crop->setAutoOrient(true); // Default
```

## Profiling Methods

### start()

```php
public static function start(): void
```

Starts profiling timer.

### mark()

```php
public static function mark(): string
```

Returns elapsed time since `start()` was called.

**Returns:** `string` - Formatted time string

**Example:**
```php
Crop::start();
$crop = new CropEntropy('image.jpg');
$result = $crop->resizeAndCrop(300, 200);
echo "Processing time: " . Crop::mark();
```

## Error Handling

### Common Exceptions

The library may throw these exceptions:

- `RuntimeException` - When no image is set or processing fails
- `ImagickException` - When Imagick operations fail
- `InvalidArgumentException` - When invalid parameters are provided

### Error Handling Example

```php
try {
    $crop = new CropCenter('image.jpg');
    $result = $crop->resizeAndCrop(300, 200);
    $result->writeImage('output.jpg');
} catch (RuntimeException $e) {
    echo "Runtime error: " . $e->getMessage();
} catch (ImagickException $e) {
    echo "Imagick error: " . $e->getMessage();
} catch (Exception $e) {
    echo "General error: " . $e->getMessage();
}
```

## Type Safety

All methods use strict typing (PHP 8.3+):

```php
// Correct usage
$crop->resizeAndCrop(300, 200);

// These will cause type errors
$crop->resizeAndCrop("300", "200");  // Strings instead of integers
$crop->resizeAndCrop(300.5, 200);    // Float instead of integer
```

## Performance Considerations

### Memory Usage

- **CropCenter**: Lowest memory usage
- **CropBalanced**: Moderate memory usage
- **CropEntropy**: Highest memory usage (due to analysis)

### Processing Time

- **CropCenter**: ~10ms for 1920x1080 image
- **CropBalanced**: ~50ms for 1920x1080 image
- **CropEntropy**: ~100ms for 1920x1080 image

### Recommendations

1. **For thumbnails**: Use `CropCenter`
2. **For general use**: Use `CropBalanced`
3. **For important images**: Use `CropEntropy`

## Next Steps

- 📖 [Crop Class](crop.html) - Base class documentation
- 🎯 [CropCenter](crop-center.html) - Center cropping
- 🧠 [CropEntropy](crop-entropy.html) - Entropy cropping
- ⚖️ [CropBalanced](crop-balanced.html) - Balanced cropping