---
layout: default
title: Installation
---

# Installation

## Requirements

Before installing the Crop library, ensure your system meets these requirements:

- **PHP 8.3 or higher** with strict typing support
- **ImageMagick extension** with sRGB colorspace (version 6.7.5-5 or higher)
- **Composer** for package management

## Install via Composer

The recommended way to install Crop is via [Composer](https://getcomposer.org):

```bash
composer require drzippie/crop
```

## Verify Installation

After installation, verify that the library is working correctly:

```php
<?php
require_once 'vendor/autoload.php';

use drzippie\crop\CropCenter;

// Test basic functionality
$crop = new CropCenter();
echo "✅ Crop library installed successfully!\n";
```

## System Requirements Check

### Check PHP Version

```bash
php --version
```

Ensure you're running PHP 8.3 or higher.

### Check ImageMagick Extension

```bash
php -m | grep imagick
```

Or check programmatically:

```php
<?php
if (extension_loaded('imagick')) {
    echo "✅ ImageMagick extension is available\n";
    
    // Check ImageMagick version
    $imagick = new Imagick();
    $version = $imagick->getVersion();
    echo "ImageMagick version: " . $version['versionString'] . "\n";
} else {
    echo "❌ ImageMagick extension is not available\n";
}
```

## Development Installation

For development and testing:

```bash
git clone https://github.com/drzippie/crop.git
cd crop
composer install
```

### Running Tests

```bash
composer test
```

### Running Static Analysis

```bash
composer phpstan
```

## Docker Setup

For containerized development:

```dockerfile
FROM php:8.3-cli

# Install ImageMagick
RUN apt-get update && apt-get install -y \
    libmagickwand-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application
COPY . /app
WORKDIR /app

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
```

## Troubleshooting

### Common Issues

**ImageMagick not found:**
```bash
# Ubuntu/Debian
sudo apt-get install php-imagick

# CentOS/RHEL
sudo yum install php-imagick

# macOS with Homebrew
brew install imagemagick
pecl install imagick
```

**Memory issues with large images:**
```php
// Increase memory limit
ini_set('memory_limit', '512M');

// Or in your php.ini
memory_limit = 512M
```

**Permission issues:**
```bash
# Ensure proper permissions for image files
chmod 644 /path/to/images/*
```

## Next Steps

- 📖 [Usage Guide](usage.html) - Learn basic and advanced usage
- 🎯 [Cropping Strategies](strategies.html) - Understand different algorithms
- 💡 [Examples](examples/) - See practical examples