# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Setup
- `make install` or `composer install` - Install dependencies
- `make dev-setup` - Set up development environment

### Testing
- `make test` or `composer test` - Run PHPUnit tests
- `make test-coverage` or `composer test-coverage` - Run tests with coverage report
- `phpunit tests/Unit/CropCenterTest.php` - Run specific test class

### Code Quality
- `make phpstan` or `composer phpstan` - Run PHPStan static analysis
- `make phpcs` or `composer phpcs` - Run PHP CodeSniffer (PSR-12 standard)
- `make phpcbf` or `composer phpcbf` - Auto-fix code style issues

### Legacy Build Tools
- `phing` - Run default build target (includes phpcs + docgen)
- `phing phpcs` - Run PHP CodeSniffer with PSR2 standard
- `phing docgen` - Generate API documentation

## Architecture Overview

This is a PHP library for intelligent image cropping using ImageMagick. The library provides multiple cropping strategies through a class hierarchy:

### Core Classes
- **`drzippie\crop\Crop`** (abstract base class): Contains shared functionality for all cropping strategies
  - Image loading and basic operations
  - Profiling utilities
  - Entropy calculation methods
  - Base resize/crop workflow in `resizeAndCrop()`

### Cropping Strategies
- **`drzippie\crop\CropCenter`**: Simple center-based cropping
- **`drzippie\crop\CropEntropy`**: Crops based on image entropy (edge detection) to preserve high-energy areas
- **`drzippie\crop\CropBalanced`**: Divides image into quadrants and finds weighted center of interest
- **`drzippie\crop\CropFace`**: Extends CropEntropy but protects detected faces from being cropped out (uses pure PHP HAARPHP library)

### Key Architecture Patterns
- **Template Method**: The base `Crop` class defines the overall workflow in `resizeAndCrop()`, while concrete classes implement `getSpecialOffset()` to provide their specific cropping logic
- **Strategy Pattern**: Each cropping class implements a different strategy for determining the optimal crop position
- **ImageMagick Integration**: All classes work with `\Imagick` objects for image manipulation

### Dependencies
- PHP 8.3+
- ImageMagick extension (sRGB colorspace, version 6.7.5-5 or higher)
- GD extension (for `CropFace` only)

### Testing
- **PHPUnit 11+** with modern test suite architecture
- **Unit Tests**: Individual class testing in `tests/Unit/`
- **Integration Tests**: End-to-end testing in `tests/Integration/`
- **Test Coverage**: HTML and console coverage reports
- **Test Fixtures**: Sample images in `tests/fixtures/`
- **CI/CD**: Automated testing with GitHub Actions for PHP 8.3+