---
layout: default
title: Advanced Usage Examples
---

# Advanced Usage Examples

Advanced techniques and patterns for using the Crop library in production environments.

## Custom Crop Strategies

### Strategy Selection Based on Image Type

```php
use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};

class SmartCropper {
    public function cropByImageType($imagePath, $width, $height) {
        $imageInfo = getimagesize($imagePath);
        $aspectRatio = $imageInfo[0] / $imageInfo[1];
        
        // Choose strategy based on image characteristics
        if ($aspectRatio > 2.0) {
            // Wide images - use entropy to find focal point
            $crop = new CropEntropy($imagePath);
        } elseif ($aspectRatio < 0.5) {
            // Tall images - use center crop
            $crop = new CropCenter($imagePath);
        } else {
            // Square-ish images - use balanced approach
            $crop = new CropBalanced($imagePath);
        }
        
        return $crop->resizeAndCrop($width, $height);
    }
}
```

### Multi-Strategy Comparison

```php
use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};

class CropComparator {
    public function generateComparison($imagePath, $width, $height) {
        $strategies = [
            'center' => new CropCenter($imagePath),
            'entropy' => new CropEntropy($imagePath),
            'balanced' => new CropBalanced($imagePath)
        ];
        
        $results = [];
        foreach ($strategies as $name => $crop) {
            $result = $crop->resizeAndCrop($width, $height);
            $result->writeImage("comparison_{$name}.jpg");
            $results[$name] = "comparison_{$name}.jpg";
        }
        
        return $results;
    }
}
```

## Performance Optimization

### Lazy Loading and Caching

```php
use drzippie\crop\CropBalanced;

class CachedCropper {
    private $cacheDir;
    
    public function __construct($cacheDir) {
        $this->cacheDir = $cacheDir;
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    }
    
    public function getCroppedImage($imagePath, $width, $height, $strategy = 'balanced') {
        $cacheKey = md5($imagePath . $width . $height . $strategy);
        $cachePath = $this->cacheDir . '/' . $cacheKey . '.jpg';
        
        if (file_exists($cachePath)) {
            return $cachePath;
        }
        
        $cropClass = $this->getCropClass($strategy);
        $crop = new $cropClass($imagePath);
        $result = $crop->resizeAndCrop($width, $height);
        $result->writeImage($cachePath);
        
        return $cachePath;
    }
    
    private function getCropClass($strategy) {
        switch ($strategy) {
            case 'center': return CropCenter::class;
            case 'entropy': return CropEntropy::class;
            case 'balanced': 
            default: return CropBalanced::class;
        }
    }
}
```

### Parallel Processing

```php
use drzippie\crop\CropCenter;

class ParallelCropper {
    private $workers = 4;
    
    public function processBatch($images, $width, $height) {
        $chunks = array_chunk($images, ceil(count($images) / $this->workers));
        $processes = [];
        
        foreach ($chunks as $i => $chunk) {
            $processes[$i] = new Process([
                'php',
                'worker.php',
                json_encode($chunk),
                $width,
                $height
            ]);
            $processes[$i]->start();
        }
        
        $results = [];
        foreach ($processes as $process) {
            $process->wait();
            $results = array_merge($results, json_decode($process->getOutput(), true));
        }
        
        return $results;
    }
}
```

## Advanced Error Handling

### Retry Mechanism

```php
use drzippie\crop\CropEntropy;

class ResilientCropper {
    private $maxRetries = 3;
    private $retryDelay = 1000000; // 1 second in microseconds
    
    public function cropWithRetry($imagePath, $width, $height) {
        $attempts = 0;
        $lastException = null;
        
        while ($attempts < $this->maxRetries) {
            try {
                $crop = new CropEntropy($imagePath);
                return $crop->resizeAndCrop($width, $height);
            } catch (Exception $e) {
                $lastException = $e;
                $attempts++;
                
                if ($attempts < $this->maxRetries) {
                    usleep($this->retryDelay * $attempts);
                }
            }
        }
        
        throw new RuntimeException(
            "Failed to crop image after {$this->maxRetries} attempts",
            0,
            $lastException
        );
    }
}
```

### Fallback Strategies

```php
use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};

class FallbackCropper {
    private $strategies = [
        CropEntropy::class,
        CropBalanced::class,
        CropCenter::class
    ];
    
    public function cropWithFallback($imagePath, $width, $height) {
        $lastException = null;
        
        foreach ($this->strategies as $strategyClass) {
            try {
                $crop = new $strategyClass($imagePath);
                return $crop->resizeAndCrop($width, $height);
            } catch (Exception $e) {
                $lastException = $e;
                continue;
            }
        }
        
        throw new RuntimeException(
            "All cropping strategies failed",
            0,
            $lastException
        );
    }
}
```

## Custom Image Processing Pipeline

### Multi-Stage Processing

```php
use drzippie\crop\CropBalanced;

class ImagePipeline {
    private $stages = [];
    
    public function addStage(callable $stage) {
        $this->stages[] = $stage;
        return $this;
    }
    
    public function process($imagePath, $width, $height) {
        $crop = new CropBalanced($imagePath);
        $result = $crop->resizeAndCrop($width, $height);
        
        foreach ($this->stages as $stage) {
            $result = $stage($result);
        }
        
        return $result;
    }
}

// Usage
$pipeline = new ImagePipeline();
$pipeline
    ->addStage(function($image) {
        // Add watermark
        $watermark = new Imagick('watermark.png');
        $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 10, 10);
        return $image;
    })
    ->addStage(function($image) {
        // Adjust brightness
        $image->brightnessContrastImage(10, 5);
        return $image;
    })
    ->addStage(function($image) {
        // Apply filter
        $image->sepiaTonoImage(80);
        return $image;
    });

$result = $pipeline->process('input.jpg', 400, 300);
```

### Quality-Based Processing

```php
use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};

class QualityAwareCropper {
    private $qualityThresholds = [
        'low' => 0.3,
        'medium' => 0.7,
        'high' => 1.0
    ];
    
    public function cropByQuality($imagePath, $width, $height, $quality = 'medium') {
        $imageInfo = getimagesize($imagePath);
        $originalSize = $imageInfo[0] * $imageInfo[1];
        $targetSize = $width * $height;
        $compressionRatio = $targetSize / $originalSize;
        
        // Choose strategy based on quality requirements and compression
        if ($quality === 'high' || $compressionRatio > $this->qualityThresholds['high']) {
            $crop = new CropEntropy($imagePath);
        } elseif ($quality === 'medium' || $compressionRatio > $this->qualityThresholds['medium']) {
            $crop = new CropBalanced($imagePath);
        } else {
            $crop = new CropCenter($imagePath);
        }
        
        return $crop
            ->setFilter($this->getFilterForQuality($quality))
            ->setBlur($this->getBlurForQuality($quality))
            ->resizeAndCrop($width, $height);
    }
    
    private function getFilterForQuality($quality) {
        switch ($quality) {
            case 'high': return Imagick::FILTER_LANCZOS;
            case 'medium': return Imagick::FILTER_CUBIC;
            case 'low': return Imagick::FILTER_POINT;
            default: return Imagick::FILTER_CUBIC;
        }
    }
    
    private function getBlurForQuality($quality) {
        switch ($quality) {
            case 'high': return 0.3;
            case 'medium': return 0.5;
            case 'low': return 0.8;
            default: return 0.5;
        }
    }
}
```

## Integration Examples

### Laravel Integration

```php
use drzippie\crop\CropBalanced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller {
    public function crop(Request $request) {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
            'width' => 'required|integer|min:1|max:2000',
            'height' => 'required|integer|min:1|max:2000',
            'strategy' => 'in:center,entropy,balanced'
        ]);
        
        $file = $request->file('image');
        $tempPath = $file->store('temp');
        
        try {
            $cropClass = $this->getCropClass($request->input('strategy', 'balanced'));
            $crop = new $cropClass(Storage::path($tempPath));
            
            $result = $crop->resizeAndCrop(
                $request->input('width'),
                $request->input('height')
            );
            
            $outputPath = 'cropped/' . uniqid() . '.jpg';
            $result->writeImage(Storage::path($outputPath));
            
            return response()->json([
                'success' => true,
                'path' => Storage::url($outputPath)
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        } finally {
            Storage::delete($tempPath);
        }
    }
    
    private function getCropClass($strategy) {
        switch ($strategy) {
            case 'center': return CropCenter::class;
            case 'entropy': return CropEntropy::class;
            case 'balanced': 
            default: return CropBalanced::class;
        }
    }
}
```

### Symfony Integration

```php
use drzippie\crop\CropEntropy;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImageService {
    private $uploadDir;
    
    public function __construct($uploadDir) {
        $this->uploadDir = $uploadDir;
    }
    
    public function processUpload(UploadedFile $file, $width, $height) {
        $originalName = $file->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        
        $uploadPath = $this->uploadDir . '/' . $filename . '.' . $extension;
        $file->move($this->uploadDir, $filename . '.' . $extension);
        
        try {
            $crop = new CropEntropy($uploadPath);
            $result = $crop->resizeAndCrop($width, $height);
            
            $croppedPath = $this->uploadDir . '/' . $filename . '_cropped.' . $extension;
            $result->writeImage($croppedPath);
            
            return new JsonResponse([
                'success' => true,
                'original' => $uploadPath,
                'cropped' => $croppedPath
            ]);
            
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

## Monitoring and Logging

### Performance Monitoring

```php
use drzippie\crop\CropBalanced;

class MonitoredCropper {
    private $logger;
    
    public function __construct($logger) {
        $this->logger = $logger;
    }
    
    public function cropWithMonitoring($imagePath, $width, $height) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        try {
            $crop = new CropBalanced($imagePath);
            $result = $crop->resizeAndCrop($width, $height);
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            
            $this->logger->info('Crop operation completed', [
                'image' => $imagePath,
                'dimensions' => "{$width}x{$height}",
                'duration' => round(($endTime - $startTime) * 1000, 2) . 'ms',
                'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2) . 'MB',
                'strategy' => 'balanced'
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logger->error('Crop operation failed', [
                'image' => $imagePath,
                'dimensions' => "{$width}x{$height}",
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
```

## Testing and Validation

### Unit Testing Helper

```php
use drzippie\crop\CropCenter;
use PHPUnit\Framework\TestCase;

class CropTestHelper extends TestCase {
    private $tempDir;
    
    protected function setUp(): void {
        $this->tempDir = sys_get_temp_dir() . '/crop_tests';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }
    
    protected function tearDown(): void {
        $this->cleanupTempDir();
    }
    
    protected function createTestImage($width, $height) {
        $image = new Imagick();
        $image->newImage($width, $height, 'white');
        $image->setImageFormat('png');
        
        // Add some pattern
        $draw = new ImagickDraw();
        $draw->setFillColor('black');
        $draw->rectangle(10, 10, 30, 30);
        $image->drawImage($draw);
        
        return $image;
    }
    
    protected function assertImageDimensions($image, $expectedWidth, $expectedHeight) {
        $geometry = $image->getImageGeometry();
        $this->assertEquals($expectedWidth, $geometry['width']);
        $this->assertEquals($expectedHeight, $geometry['height']);
    }
    
    private function cleanupTempDir() {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }
}
```

## Next Steps

- 📚 [API Reference](../api/) - Complete method documentation
- 🎯 [Cropping Strategies](../strategies.html) - Understanding the algorithms
- 🏠 [Home](../index.html) - Back to overview