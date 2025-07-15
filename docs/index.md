---
layout: default
title: Home
---

# Crop - Intelligent Image Cropping Library

A modern PHP library for intelligent image cropping with multiple algorithms. This is a maintained fork of the original [stojg/crop](https://github.com/stojg/crop) library with modern improvements.

## ✨ Key Features

- 🎯 **Three intelligent cropping algorithms**: Center, Entropy, and Balanced
- 🚀 **Modern PHP 8.3+** compatibility with strict typing
- 🔒 **PHPStan level 8** compliant for maximum type safety
- 🧪 **Comprehensive test suite** with PHPUnit 11+
- 📦 **Simple installation** via Composer
- 🔧 **Flexible API** with method chaining support

## 🚀 Quick Start

### Installation

```bash
composer require drzippie/crop
```

### Basic Usage

```php
use drzippie\crop\CropEntropy;

// Create cropper and process image
$crop = new CropEntropy('path/to/image.jpg');
$result = $crop->resizeAndCrop(300, 200);
$result->writeImage('path/to/output.jpg');
```

## 📊 Cropping Strategies

| Strategy | Description | Best For |
|----------|-------------|----------|
| **CropCenter** | Simple center-based cropping | Fast processing, uniform crops |
| **CropEntropy** | Edge-detection based cropping | Preserving important details |
| **CropBalanced** | Weighted center of interest | Balanced composition |

## 📖 Documentation

- **[Installation Guide](installation.html)** - Setup and requirements
- **[Usage Guide](usage.html)** - Basic and advanced usage
- **[Cropping Strategies](strategies.html)** - Detailed algorithm explanations
- **[Examples](examples/)** - Practical examples with code
- **[API Reference](api/)** - Complete API documentation

## 🔧 Requirements

- PHP 8.3 or higher
- ImageMagick extension with sRGB colorspace (version 6.7.5-5 or higher)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit issues and enhancement requests.

## 📄 License

This project is licensed under the BSD-2-Clause License - see the [LICENSE](https://github.com/drzippie/crop/blob/master/LICENCE) file for details.

---

*This library is actively maintained and continues the legacy of the original stojg/crop project.*