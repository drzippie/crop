<?php

declare(strict_types=1);

namespace drzippie\crop\Tests\Unit;

use drzippie\crop\CropBalanced;
use drzippie\crop\Tests\TestCase;
use Imagick;
use Exception;

class CropBalancedTest extends TestCase
{
    private CropBalanced $cropBalanced;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->cropBalanced = new CropBalanced();
    }
    
    public function testConstructorWithImagePath(): void
    {
        $imagePath = $this->createTestImageFile();
        $crop = new CropBalanced($imagePath);
        
        $this->assertInstanceOf(Imagick::class, $crop->getOriginalImage());
        $this->cleanupTestFile($imagePath);
    }
    
    public function testBalancedCropping(): void
    {
        $image = $this->createTestImageWithPattern(200, 150);
        $this->cropBalanced->setImage($image);
        
        $result = $this->cropBalanced->resizeAndCrop(100, 100);
        
        $this->assertInstanceOf(Imagick::class, $result);
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testGetOffsetBalanced(): void
    {
        $image = $this->createTestImageWithPattern(200, 150);
        $this->cropBalanced->setImage($image);
        
        $offset = $this->cropBalanced->getOffsetBalanced(100, 100);
        
        $this->assertArrayHasKeys(['x', 'y'], $offset);
        $this->assertIsInt($offset['x']);
        $this->assertIsInt($offset['y']);
        $this->assertGreaterThanOrEqual(0, $offset['x']);
        $this->assertGreaterThanOrEqual(0, $offset['y']);
    }
    
    public function testGetOffsetBalancedWithoutImage(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No original image set');
        
        $this->cropBalanced->getOffsetBalanced(100, 100);
    }
    
    public function testGetRandomEdgeOffset(): void
    {
        $image = $this->createTestImageWithPattern(200, 150);
        $crop = new TestCropBalanced();
        
        $offset = $crop->getRandomEdgeOffset($image, 100, 100);
        
        $this->assertArrayHasKeys(['x', 'y'], $offset);
        $this->assertIsInt($offset['x']);
        $this->assertIsInt($offset['y']);
    }
    
    public function testGetOffsetBalancedForImage(): void
    {
        $image = $this->createTestImageWithPattern(200, 150);
        $crop = new TestCropBalanced();
        
        $offset = $crop->getOffsetBalancedForImage($image, 100, 100);
        
        $this->assertArrayHasKeys(['x', 'y'], $offset);
        $this->assertIsInt($offset['x']);
        $this->assertIsInt($offset['y']);
    }
    
    public function testGetHighestEnergyPoint(): void
    {
        $image = $this->createTestImageWithPattern(100, 100);
        $crop = new TestCropBalanced();
        
        $point = $crop->getHighestEnergyPoint($image);
        
        $this->assertArrayHasKeys(['x', 'y', 'sum'], $point);
        $this->assertIsFloat($point['x']);
        $this->assertIsFloat($point['y']);
        $this->assertIsFloat($point['sum']);
        $this->assertGreaterThanOrEqual(0, $point['x']);
        $this->assertGreaterThanOrEqual(0, $point['y']);
        $this->assertGreaterThanOrEqual(0, $point['sum']);
    }
    
    public function testGetHighestEnergyPointWithPlainImage(): void
    {
        $image = $this->createTestImage(100, 100); // Plain white image
        $crop = new TestCropBalanced();
        
        $point = $crop->getHighestEnergyPoint($image);
        
        $this->assertArrayHasKeys(['x', 'y', 'sum'], $point);
        $this->assertIsFloat($point['x']);
        $this->assertIsFloat($point['y']);
        $this->assertIsFloat($point['sum']);
    }
    
    public function testBalancedCroppingWithSquareImage(): void
    {
        $image = $this->createTestImageWithPattern(200, 200);
        $this->cropBalanced->setImage($image);
        
        $result = $this->cropBalanced->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testBalancedCroppingWithWideImage(): void
    {
        $image = $this->createTestImageWithPattern(300, 100);
        $this->cropBalanced->setImage($image);
        
        $result = $this->cropBalanced->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testBalancedCroppingWithTallImage(): void
    {
        $image = $this->createTestImageWithPattern(100, 300);
        $this->cropBalanced->setImage($image);
        
        $result = $this->cropBalanced->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testMultipleCroppingSizes(): void
    {
        $image = $this->createTestImageWithPattern(400, 300);
        $this->cropBalanced->setImage($image);
        
        $sizes = [
            [100, 100],
            [150, 100],
            [100, 150],
            [200, 200]
        ];
        
        foreach ($sizes as [$width, $height]) {
            $result = $this->cropBalanced->resizeAndCrop($width, $height);
            $this->assertImageDimensions($result, $width, $height);
        }
    }
    
    public function testQuadrantProcessing(): void
    {
        $image = $this->createTestImageWithPattern(200, 200);
        $crop = new TestCropBalanced();
        
        $offset = $crop->getOffsetBalancedForImage($image, 100, 100);
        
        // The algorithm should process 4 quadrants and find a balanced center
        $this->assertArrayHasKeys(['x', 'y'], $offset);
        $this->assertLessThanOrEqual(100, $offset['x']); // Should be within bounds
        $this->assertLessThanOrEqual(100, $offset['y']); // Should be within bounds
    }
}

/**
 * Test wrapper to expose protected methods
 */
class TestCropBalanced extends CropBalanced
{
    public function getOriginalImage(): ?Imagick
    {
        return $this->originalImage;
    }
    
    public function setImage(Imagick $image): self
    {
        parent::setImage($image);
        return $this;
    }
    
    public function getRandomEdgeOffset(Imagick $original, int $targetWidth, int $targetHeight): array
    {
        return parent::getRandomEdgeOffset($original, $targetWidth, $targetHeight);
    }
    
    public function getOffsetBalancedForImage(Imagick $image, int $targetWidth, int $targetHeight): array
    {
        return $this->getOffsetBalanced($targetWidth, $targetHeight);
    }
    
    public function getHighestEnergyPoint(Imagick $image): array
    {
        return parent::getHighestEnergyPoint($image);
    }
}