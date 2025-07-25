# Crop

[![CI Status](https://github.com/drzippie/crop/workflows/CI/badge.svg)](https://github.com/drzippie/crop/actions)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/github/license/drzippie/crop.svg)](https://github.com/drzippie/crop/blob/master/LICENCE)
[![Latest Stable Version](https://img.shields.io/packagist/v/drzippie/crop.svg)](https://packagist.org/packages/drzippie/crop)
[![Total Downloads](https://img.shields.io/packagist/dt/drzippie/crop.svg)](https://packagist.org/packages/drzippie/crop)

This is a maintained fork of the original [stojg/crop](https://github.com/stojg/crop) library, which was archived on April 30, 2021. This fork continues development and adds modern improvements.

**Key enhancements in this fork:**
- ✅ Modern PHP 8.3+ compatibility with strict typing
- ✅ Comprehensive test suite with PHPUnit 11+
- ✅ PHPStan level 8 compliance for type safety
- ✅ Modernized dependencies and compatibility
- ✅ Active maintenance and bug fixes

This is a small set of image croppers for automated cropping with intelligent algorithms.

## Requirements

 - PHP 8.3 or higher
 - ImageMagick extension with sRGB colorspace (version 6.7.5-5 or higher)

## Description

This project includes three intelligent image cropping algorithms:

### CropCenter

 This is the most basic of cropping techniques:

   1. Find the exact center of the image
   2. Trim any edges that is bigger than the targetWidth and targetHeight

### CropEntropy

This class finds the a position in the picture with the most "energy" in it. Energy (or entropy) in
images are defined by 'edginess' in the image. For example a image of the sky have low edginess and
an image of an anthill has very high edginess.

Energy is in this case calculated like this

  1. Take the image and turn it into black and white
  2. Run a edge filter so that we're left with only edges.
  3. Find a piece in the picture that has the highest entropy (i.e. most edges)
  4. Return coordinates that makes sure that this piece of the picture is not cropped 'away'

### CropBalanced

Crop balanced is a variant of CropEntropy where I tried to the cropping a bit more balanced.

  1. Dividing the image into four equally squares
  2. Find the most energetic point per square
  3. Finding the images weighted mean interest point for all squares

## Usage

### Basic Usage

```php
use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};

// Center-based cropping (fastest)
$center = new CropCenter($filepath);
$croppedImage = $center->resizeAndCrop($width, $height);
$croppedImage->writeimage('assets/thumbs/cropped-center.jpg');

// Entropy-based cropping (intelligent edge detection)
$entropy = new CropEntropy($filepath);
$croppedImage = $entropy->resizeAndCrop($width, $height);
$croppedImage->writeimage('assets/thumbs/cropped-entropy.jpg');

// Balanced cropping (weighted center of interest)
$balanced = new CropBalanced($filepath);
$croppedImage = $balanced->resizeAndCrop($width, $height);
$croppedImage->writeimage('assets/thumbs/cropped-balanced.jpg');
```

### Advanced Usage

```php
use drzippie\crop\CropEntropy;

// Create cropper with custom settings
$crop = new CropEntropy();
$crop->setImage($imagickObject)
     ->setFilter(Imagick::FILTER_LANCZOS)
     ->setBlur(0.8)
     ->setAutoOrient(true);

$result = $crop->resizeAndCrop(300, 200);
```

## Installation

Install via Composer:

```bash
composer require drzippie/crop
```

## Documentation

📖 **[Complete Documentation](https://drzippie.github.io/crop/)** - GitHub Pages with full API reference and examples
