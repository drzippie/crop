---
layout: default
title: Examples
---

# Examples

Practical examples showing how to use the Crop library in real-world scenarios.

## Basic Examples

### [Basic Usage](basic-usage.html)
Simple examples showing how to get started with each cropping strategy.

### [Advanced Usage](advanced-usage.html)
Advanced techniques including method chaining, custom settings, and batch processing.

## Real-World Scenarios

### Thumbnail Generation

```php
use drzippie\crop\CropCenter;

function generateThumbnails($sourcePath, $outputDir, $sizes) {
    $crop = new CropCenter($sourcePath);
    $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
    
    foreach ($sizes as $name => [$width, $height]) {
        $result = $crop->resizeAndCrop($width, $height);
        $result->writeImage("$outputDir/{$filename}_{$name}.jpg");
    }
}

// Usage
generateThumbnails('photo.jpg', 'thumbnails', [
    'small' => [100, 100],
    'medium' => [200, 200],
    'large' => [400, 400]
]);
```

### Content Management System

```php
use drzippie\crop\CropBalanced;

class ImageProcessor {
    private $uploadDir;
    private $thumbDir;
    
    public function __construct($uploadDir, $thumbDir) {
        $this->uploadDir = $uploadDir;
        $this->thumbDir = $thumbDir;
    }
    
    public function processUpload($file, $targetWidth = 800, $targetHeight = 600) {
        $crop = new CropBalanced($this->uploadDir . '/' . $file);
        $result = $crop->resizeAndCrop($targetWidth, $targetHeight);
        
        $output = $this->thumbDir . '/processed_' . $file;
        $result->writeImage($output);
        
        return $output;
    }
}
```

### E-commerce Product Images

```php
use drzippie\crop\CropEntropy;

class ProductImageProcessor {
    public function processProductImage($imagePath, $productId) {
        $crop = new CropEntropy($imagePath);
        
        $sizes = [
            'thumb' => [150, 150],
            'gallery' => [400, 300],
            'detail' => [800, 600]
        ];
        
        $results = [];
        foreach ($sizes as $size => [$width, $height]) {
            $result = $crop->resizeAndCrop($width, $height);
            $filename = "product_{$productId}_{$size}.jpg";
            $result->writeImage("images/products/{$filename}");
            $results[$size] = $filename;
        }
        
        return $results;
    }
}
```

### Social Media Image Optimizer

```php
use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};

class SocialMediaCropper {
    private $platforms = [
        'facebook' => [1200, 630],
        'twitter' => [1024, 512],
        'instagram' => [1080, 1080],
        'linkedin' => [1200, 627]
    ];
    
    public function optimizeForPlatforms($imagePath, $strategy = 'balanced') {
        $cropClass = $this->getCropStrategy($strategy);
        $results = [];
        
        foreach ($this->platforms as $platform => [$width, $height]) {
            $crop = new $cropClass($imagePath);
            $result = $crop->resizeAndCrop($width, $height);
            
            $filename = "social_{$platform}_" . basename($imagePath);
            $result->writeImage("social/{$filename}");
            $results[$platform] = $filename;
        }
        
        return $results;
    }
    
    private function getCropStrategy($strategy) {
        switch ($strategy) {
            case 'center': return CropCenter::class;
            case 'entropy': return CropEntropy::class;
            case 'balanced': 
            default: return CropBalanced::class;
        }
    }
}
```

## Performance Optimization

### Batch Processing with Memory Management

```php
use drzippie\crop\CropCenter;

function processBatch($inputDir, $outputDir, $width, $height) {
    $files = glob($inputDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);
    $processed = 0;
    
    foreach ($files as $file) {
        try {
            $crop = new CropCenter($file);
            $result = $crop->resizeAndCrop($width, $height);
            
            $filename = basename($file);
            $result->writeImage($outputDir . '/' . $filename);
            
            // Clean up memory
            $result->clear();
            $result->destroy();
            unset($crop);
            
            $processed++;
            
            // Force garbage collection every 10 images
            if ($processed % 10 === 0) {
                gc_collect_cycles();
            }
            
        } catch (Exception $e) {
            error_log("Failed to process $file: " . $e->getMessage());
        }
    }
    
    return $processed;
}
```

### Asynchronous Processing

```php
use drzippie\crop\CropBalanced;

class AsyncImageProcessor {
    private $queue = [];
    
    public function addToQueue($imagePath, $width, $height, $outputPath) {
        $this->queue[] = [
            'input' => $imagePath,
            'width' => $width,
            'height' => $height,
            'output' => $outputPath
        ];
    }
    
    public function processQueue($batchSize = 5) {
        $batches = array_chunk($this->queue, $batchSize);
        
        foreach ($batches as $batch) {
            $processes = [];
            
            foreach ($batch as $item) {
                $processes[] = $this->processAsync($item);
            }
            
            // Wait for all processes to complete
            foreach ($processes as $process) {
                $process->wait();
            }
        }
    }
    
    private function processAsync($item) {
        // Implementation would depend on your async library
        // This is a simplified example
        return new Process([
            'php',
            'process_image.php',
            $item['input'],
            $item['output'],
            $item['width'],
            $item['height']
        ]);
    }
}
```

## Error Handling and Validation

### Robust Image Processing

```php
use drzippie\crop\CropEntropy;

class SafeImageProcessor {
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    public function processImage($imagePath, $width, $height) {
        // Validate input
        if (!$this->validateImage($imagePath)) {
            throw new InvalidArgumentException('Invalid image file');
        }
        
        try {
            $crop = new CropEntropy($imagePath);
            $result = $crop->resizeAndCrop($width, $height);
            
            // Validate output
            if (!$this->validateOutput($result)) {
                throw new RuntimeException('Failed to process image');
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Image processing failed: " . $e->getMessage());
            throw new RuntimeException('Image processing failed', 0, $e);
        }
    }
    
    private function validateImage($imagePath) {
        if (!file_exists($imagePath)) {
            return false;
        }
        
        $info = getimagesize($imagePath);
        if (!$info) {
            return false;
        }
        
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return false;
        }
        
        if (filesize($imagePath) > $this->maxFileSize) {
            return false;
        }
        
        return true;
    }
    
    private function validateOutput($result) {
        if (!$result instanceof Imagick) {
            return false;
        }
        
        $geometry = $result->getImageGeometry();
        return $geometry['width'] > 0 && $geometry['height'] > 0;
    }
}
```

## Next Steps

- 📚 [API Reference](../api/) - Complete method documentation
- 🏠 [Home](../index.html) - Back to overview
- 📖 [Usage Guide](../usage.html) - Basic usage patterns