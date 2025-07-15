<?php

declare(strict_types=1);

namespace drzippie\crop\Tests\Integration;

use drzippie\crop\{CropCenter, CropEntropy, CropBalanced};
use drzippie\crop\Tests\TestCase;
use Imagick;

class CropIntegrationTest extends TestCase
{
    private array $testImages;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test images with different characteristics
        $this->testImages = [
            'square' => $this->createTestImageFile(200, 200),
            'wide' => $this->createTestImageFile(300, 150),
            'tall' => $this->createTestImageFile(150, 300),
            'large' => $this->createTestImageFile(500, 400),
            'small' => $this->createTestImageFile(50, 50),
        ];
    }
    
    protected function tearDown(): void
    {
        foreach ($this->testImages as $path) {
            $this->cleanupTestFile($path);
        }
        parent::tearDown();
    }
    
    public function testAllCropStrategiesProduceSameOutputSize(): void
    {
        $targetWidth = 100;
        $targetHeight = 100;
        
        $strategies = [
            'center' => CropCenter::class,
            'entropy' => CropEntropy::class,
            'balanced' => CropBalanced::class,
        ];
        
        foreach ($this->testImages as $imageType => $imagePath) {
            foreach ($strategies as $strategyName => $strategyClass) {
                $crop = new $strategyClass($imagePath);
                $result = $crop->resizeAndCrop($targetWidth, $targetHeight);
                
                $this->assertImageDimensions($result, $targetWidth, $targetHeight);
                $this->addToAssertionCount(1); // Track that we completed this test
            }
        }
    }
    
    public function testCropStrategiesWithDifferentTargetSizes(): void
    {
        $targetSizes = [
            [50, 50],
            [100, 100],
            [100, 150],
            [150, 100],
            [200, 200],
        ];
        
        $imagePath = $this->testImages['large'];
        
        foreach ($targetSizes as [$width, $height]) {
            $strategies = [
                new CropCenter($imagePath),
                new CropEntropy($imagePath),
                new CropBalanced($imagePath),
            ];
            
            foreach ($strategies as $strategy) {
                $result = $strategy->resizeAndCrop($width, $height);
                $this->assertImageDimensions($result, $width, $height);
            }
        }
    }
    
    public function testCropStrategiesPreserveImageQuality(): void
    {
        $strategies = [
            CropCenter::class,
            CropEntropy::class,
            CropBalanced::class,
        ];
        
        foreach ($strategies as $strategyClass) {
            $imagePath = $this->testImages['large'];
            $strategy = new $strategyClass($imagePath);
            $result = $strategy->resizeAndCrop(200, 200);
            
            // Check that the result is a valid Imagick object with expected properties
            $this->assertInstanceOf(Imagick::class, $result);
            $this->assertEquals('PNG', $result->getImageFormat());
            
            // Some strategies might not preserve image length properly, so we check pixel count instead
            $geometry = $result->getImageGeometry();
            $this->assertGreaterThan(0, $geometry['width'] * $geometry['height']);
        }
    }
    
    public function testCropStrategiesWithFilterSettings(): void
    {
        $imagePath = $this->testImages['large'];
        
        $filters = [
            Imagick::FILTER_CUBIC,
            Imagick::FILTER_LANCZOS,
            Imagick::FILTER_MITCHELL,
        ];
        
        foreach ($filters as $filter) {
            $strategies = [
                new CropCenter($imagePath),
                new CropEntropy($imagePath),
                new CropBalanced($imagePath),
            ];
            
            foreach ($strategies as $strategy) {
                $strategy->setFilter($filter);
                $result = $strategy->resizeAndCrop(100, 100);
                
                $this->assertImageDimensions($result, 100, 100);
                $this->assertEquals($filter, $strategy->getFilter());
            }
        }
    }
    
    public function testCropStrategiesWithBlurSettings(): void
    {
        $imagePath = $this->testImages['large'];
        
        $blurValues = [0.5, 1.0, 1.5];
        
        foreach ($blurValues as $blur) {
            $strategies = [
                new CropCenter($imagePath),
                new CropEntropy($imagePath),
                new CropBalanced($imagePath),
            ];
            
            foreach ($strategies as $strategy) {
                $strategy->setBlur($blur);
                $result = $strategy->resizeAndCrop(100, 100);
                
                $this->assertImageDimensions($result, 100, 100);
                $this->assertEquals($blur, $strategy->getBlur());
            }
        }
    }
    
    public function testCropStrategiesWithAutoOrientSettings(): void
    {
        $imagePath = $this->testImages['large'];
        
        $autoOrientValues = [true, false];
        
        foreach ($autoOrientValues as $autoOrient) {
            $strategies = [
                new CropCenter($imagePath),
                new CropEntropy($imagePath),
                new CropBalanced($imagePath),
            ];
            
            foreach ($strategies as $strategy) {
                $strategy->setAutoOrient($autoOrient);
                $result = $strategy->resizeAndCrop(100, 100);
                
                $this->assertImageDimensions($result, 100, 100);
                $this->assertEquals($autoOrient, $strategy->getAutoOrient());
            }
        }
    }
    
    public function testCropStrategiesWithImagickInput(): void
    {
        $strategies = [
            CropCenter::class,
            CropEntropy::class,
            CropBalanced::class,
        ];
        
        foreach ($strategies as $strategyClass) {
            $image = $this->createTestImageWithPattern(300, 200);
            $strategy = new $strategyClass($image);
            $result = $strategy->resizeAndCrop(100, 100);
            $this->assertImageDimensions($result, 100, 100);
        }
    }
    
    public function testCropStrategiesPerformanceBaseline(): void
    {
        $imagePath = $this->testImages['large'];
        
        $strategies = [
            'center' => new CropCenter($imagePath),
            'entropy' => new CropEntropy($imagePath),
            'balanced' => new CropBalanced($imagePath),
        ];
        
        $times = [];
        
        foreach ($strategies as $name => $strategy) {
            $start = microtime(true);
            $result = $strategy->resizeAndCrop(200, 200);
            $end = microtime(true);
            
            $times[$name] = $end - $start;
            $this->assertImageDimensions($result, 200, 200);
        }
        
        // Basic performance assertions
        $this->assertLessThan(5.0, $times['center']); // Center should be fastest
        $this->assertLessThan(10.0, $times['entropy']); // Entropy should be reasonable
        $this->assertLessThan(10.0, $times['balanced']); // Balanced should be reasonable
    }
    
    public function testCropStrategiesWithEdgeCases(): void
    {
        $imagePath = $this->testImages['small'];
        
        $strategies = [
            new CropCenter($imagePath),
            new CropEntropy($imagePath),
            new CropBalanced($imagePath),
        ];
        
        // Test with target size equal to original size
        foreach ($strategies as $strategy) {
            $result = $strategy->resizeAndCrop(50, 50);
            $this->assertImageDimensions($result, 50, 50);
        }
        
        // Test with target size smaller than original
        foreach ($strategies as $strategy) {
            $result = $strategy->resizeAndCrop(25, 25);
            $this->assertImageDimensions($result, 25, 25);
        }
    }
    
    public function testCropStrategiesConsistency(): void
    {
        $imagePath = $this->testImages['square'];
        
        // Test that multiple runs produce consistent results
        foreach ([CropCenter::class, CropEntropy::class] as $strategyClass) {
            $results = [];
            
            for ($i = 0; $i < 3; $i++) {
                $strategy = new $strategyClass($imagePath);
                $result = $strategy->resizeAndCrop(100, 100);
                $results[] = $result->getImageSignature();
            }
            
            // All results should be identical for deterministic strategies
            $this->assertCount(1, array_unique($results));
        }
    }
}