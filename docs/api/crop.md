---
layout: default
title: Crop Class
---

# Crop (Abstract Base Class)

The `Crop` class is the foundation of all cropping strategies in the library. It provides common functionality and defines the interface that all concrete cropping implementations must follow.

## Class Overview

```php
abstract class drzippie\crop\Crop
```

This abstract class cannot be instantiated directly. Instead, use one of the concrete implementations:
- [CropCenter](crop-center.html) - Simple center-based cropping
- [CropEntropy](crop-entropy.html) - Entropy-based intelligent cropping
- [CropBalanced](crop-balanced.html) - Balanced cropping with weighted center of interest

## Constructor

```php
public function __construct(string|Imagick|null $image = null)
```

Creates a new cropping instance with optional image initialization.

**Parameters:**
- `$image` - Can be a file path (string), Imagick object, or null

**Examples:**
```php
// With file path
$crop = new CropCenter('image.jpg');

// With Imagick object
$imagick = new Imagick('image.jpg');
$crop = new CropCenter($imagick);

// Empty instance
$crop = new CropCenter();
```

## Core Methods

### resizeAndCrop()

```php
public function resizeAndCrop(int $targetWidth, int $targetHeight): Imagick
```

The main method that performs the resize and crop operation.

**Parameters:**
- `$targetWidth` - Target width in pixels
- `$targetHeight` - Target height in pixels

**Returns:** `Imagick` - The processed image

**Throws:** `RuntimeException` if no image is set

**Example:**
```php
$crop = new CropCenter('image.jpg');
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

### setFilter()

```php
public function setFilter(int $filter): self
```

Sets the resize filter to use during processing.

**Parameters:**
- `$filter` - Imagick filter constant

**Common Filters:**
- `Imagick::FILTER_LANCZOS` - High quality, slower
- `Imagick::FILTER_CUBIC` - Good quality, default
- `Imagick::FILTER_POINT` - Fast, lower quality

**Returns:** `self` for method chaining

### setBlur()

```php
public function setBlur(float $blur): self
```

Sets the blur factor for resizing operations.

**Parameters:**
- `$blur` - Blur factor (0.0 to 1.0+)

**Returns:** `self` for method chaining

### setAutoOrient()

```php
public function setAutoOrient(bool $autoOrient): self
```

Enables or disables automatic orientation based on EXIF data.

**Parameters:**
- `$autoOrient` - True to enable, false to disable

**Returns:** `self` for method chaining

## Getter Methods

### getFilter()

```php
public function getFilter(): int
```

Returns the current resize filter setting.

### getBlur()

```php
public function getBlur(): float
```

Returns the current blur factor.

### getAutoOrient()

```php
public function getAutoOrient(): bool
```

Returns the current auto-orientation setting.

## Utility Methods

### Profiling

```php
public static function start(): void
public static function mark(): string
```

Static methods for performance profiling:
- `start()` - Begins timing
- `mark()` - Returns elapsed time since start()

**Example:**
```php
Crop::start();
$crop = new CropCenter('image.jpg');
$result = $crop->resizeAndCrop(300, 200);
echo "Processing time: " . Crop::mark();
```

### Entropy Calculation

```php
public function getEntropyFromArray(array $histogram): float
```

Calculates entropy from a histogram array. Used internally by entropy-based cropping strategies.

**Parameters:**
- `$histogram` - Array of color frequency data

**Returns:** `float` - Calculated entropy value

## Abstract Methods

### getSpecialOffset()

```php
abstract protected function getSpecialOffset(
    Imagick $original, 
    int $targetWidth, 
    int $targetHeight
): array
```

Abstract method that must be implemented by concrete classes to define their specific cropping logic.

**Parameters:**
- `$original` - Original image
- `$targetWidth` - Target width
- `$targetHeight` - Target height

**Returns:** `array` - Array containing x and y offset coordinates

## Method Chaining

All setter methods return `$this`, allowing for fluent interface usage:

```php
$result = (new CropCenter('image.jpg'))
    ->setFilter(Imagick::FILTER_LANCZOS)
    ->setBlur(0.8)
    ->setAutoOrient(true)
    ->resizeAndCrop(400, 300);
```

## Error Handling

The class throws `RuntimeException` when:
- No image is set before calling `resizeAndCrop()`
- Image processing fails
- Invalid parameters are provided

## Performance Considerations

- The base class handles common operations efficiently
- Concrete implementations vary in performance:
  - CropCenter: Fastest
  - CropBalanced: Moderate
  - CropEntropy: Slowest but highest quality

## See Also

- [CropCenter](crop-center.html) - Simple center cropping
- [CropEntropy](crop-entropy.html) - Entropy-based cropping
- [CropBalanced](crop-balanced.html) - Balanced cropping
- [API Reference](index.html) - Complete API documentation