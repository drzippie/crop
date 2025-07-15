<?php

declare(strict_types=1);

namespace drzippie\crop\Tests\Unit;

use drzippie\crop\CropCenter;
use drzippie\crop\Tests\TestCase;
use Imagick;

class CropCenterTest extends TestCase
{
    private CropCenter $cropCenter;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->cropCenter = new CropCenter();
    }
    
    public function testConstructorWithImagePath(): void
    {
        $imagePath = $this->createTestImageFile();
        $crop = new CropCenter($imagePath);
        
        $this->assertInstanceOf(Imagick::class, $crop->getOriginalImage());
        $this->cleanupTestFile($imagePath);
    }
    
    public function testCenterCropping(): void
    {
        $image = $this->createTestImageWithPattern(200, 150);
        $this->cropCenter->setImage($image);
        
        $result = $this->cropCenter->resizeAndCrop(100, 100);
        
        $this->assertInstanceOf(Imagick::class, $result);
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testCenterCroppingWithSquareImage(): void
    {
        $image = $this->createTestImageWithPattern(200, 200);
        $this->cropCenter->setImage($image);
        
        $result = $this->cropCenter->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testCenterCroppingWithWideImage(): void
    {
        $image = $this->createTestImageWithPattern(300, 100);
        $this->cropCenter->setImage($image);
        
        $result = $this->cropCenter->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testCenterCroppingWithTallImage(): void
    {
        $image = $this->createTestImageWithPattern(100, 300);
        $this->cropCenter->setImage($image);
        
        $result = $this->cropCenter->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testGetCenterOffset(): void
    {
        $image = $this->createTestImage(200, 150);
        $crop = new TestCropCenter();
        
        $offset = $crop->getCenterOffset($image, 100, 100);
        
        $this->assertArrayHasKeys(['x', 'y'], $offset);
        $this->assertEquals(50, $offset['x']); // (200-100)/2
        $this->assertEquals(25, $offset['y']); // (150-100)/2
    }
    
    public function testGetCenterOffsetWithExactSize(): void
    {
        $image = $this->createTestImage(100, 100);
        $crop = new TestCropCenter();
        
        $offset = $crop->getCenterOffset($image, 100, 100);
        
        $this->assertEquals(0, $offset['x']);
        $this->assertEquals(0, $offset['y']);
    }
    
    public function testMultipleCroppingSizes(): void
    {
        $image = $this->createTestImageWithPattern(400, 300);
        $this->cropCenter->setImage($image);
        
        $sizes = [
            [100, 100],
            [150, 100],
            [100, 150],
            [200, 200]
        ];
        
        foreach ($sizes as [$width, $height]) {
            $result = $this->cropCenter->resizeAndCrop($width, $height);
            $this->assertImageDimensions($result, $width, $height);
        }
    }
}

/**
 * Test wrapper to expose protected methods
 */
class TestCropCenter extends CropCenter
{
    public function getCenterOffset(Imagick $image, int $targetWidth, int $targetHeight): array
    {
        return parent::getCenterOffset($image, $targetWidth, $targetHeight);
    }
    
    public function getOriginalImage(): ?Imagick
    {
        return $this->originalImage;
    }
    
    public function setImage(Imagick $image): self
    {
        parent::setImage($image);
        return $this;
    }
}