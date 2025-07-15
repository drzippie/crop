# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Testing
- `phpunit` - Run PHPUnit tests
- `phpunit tests/CropEntropyTest.php` - Run specific test class

### Build and Quality
- `phing` - Run default build target (includes phpcs + docgen)
- `phing phpcs` - Run PHP CodeSniffer with PSR2 standard
- `phing docgen` - Generate API documentation

### Dependencies
- `composer install` - Install dependencies (though this is a minimal library)

## Architecture Overview

This is a PHP library for intelligent image cropping using ImageMagick. The library provides multiple cropping strategies through a class hierarchy:

### Core Classes
- **`Crop`** (abstract base class): Contains shared functionality for all cropping strategies
  - Image loading and basic operations
  - Profiling utilities
  - Entropy calculation methods
  - Base resize/crop workflow in `resizeAndCrop()`

### Cropping Strategies
- **`CropCenter`**: Simple center-based cropping
- **`CropEntropy`**: Crops based on image entropy (edge detection) to preserve high-energy areas
- **`CropBalanced`**: Divides image into quadrants and finds weighted center of interest
- **`CropFace`**: Extends CropEntropy but protects detected faces from being cropped out (uses pure PHP HAARPHP library)

### Key Architecture Patterns
- **Template Method**: The base `Crop` class defines the overall workflow in `resizeAndCrop()`, while concrete classes implement `getSpecialOffset()` to provide their specific cropping logic
- **Strategy Pattern**: Each cropping class implements a different strategy for determining the optimal crop position
- **ImageMagick Integration**: All classes work with `\Imagick` objects for image manipulation

### Dependencies
- PHP 5.3+
- ImageMagick (sRGB colorspace, version 6.7.5-5 or higher)
- GD extension (for `CropFace` only)

### Testing
- Tests use PHPUnit framework
- Test images are stored in `tests/images/`
- Tests verify that cropping operations complete without errors and produce expected output files