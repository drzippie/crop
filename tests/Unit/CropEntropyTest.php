<?php

declare(strict_types=1);

namespace drzippie\crop\Tests\Unit;

use drzippie\crop\CropEntropy;
use drzippie\crop\Tests\TestCase;
use Imagick;

class CropEntropyTest extends TestCase
{
    private CropEntropy $cropEntropy;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->cropEntropy = new CropEntropy();
    }
    
    public function testConstructorWithImagePath(): void
    {
        $imagePath = $this->createTestImageFile();
        $crop = new CropEntropy($imagePath);
        
        $this->assertInstanceOf(Imagick::class, $crop->getOriginalImage());
        $this->cleanupTestFile($imagePath);
    }
    
    public function testEntropyCropping(): void
    {
        $image = $this->createTestImageWithPattern(200, 150);
        $this->cropEntropy->setImage($image);
        
        $result = $this->cropEntropy->resizeAndCrop(100, 100);
        
        $this->assertInstanceOf(Imagick::class, $result);
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testPotentialRatioConstant(): void
    {
        $this->assertEquals(1.5, CropEntropy::POTENTIAL_RATIO);
    }
    
    public function testGrayscaleEntropy(): void
    {
        $image = $this->createTestImageWithPattern(100, 100);
        $crop = new TestCropEntropy();
        
        $entropy = $crop->grayscaleEntropy($image);
        
        $this->assertIsFloat($entropy);
        $this->assertGreaterThanOrEqual(0, $entropy);
    }
    
    public function testGrayscaleEntropyWithPlainImage(): void
    {
        $image = $this->createTestImage(100, 100); // Plain white image
        $crop = new TestCropEntropy();
        
        $entropy = $crop->grayscaleEntropy($image);
        
        $this->assertIsFloat($entropy);
        $this->assertGreaterThanOrEqual(0, $entropy);
    }
    
    public function testColorEntropy(): void
    {
        $image = $this->createTestImageWithPattern(100, 100);
        $crop = new TestCropEntropy();
        
        $entropy = $crop->colorEntropy($image);
        
        $this->assertIsFloat($entropy);
        $this->assertGreaterThanOrEqual(0, $entropy);
    }
    
    public function testGetEntropyOffsets(): void
    {
        $image = $this->createTestImageWithPattern(200, 150);
        $crop = new TestCropEntropy();
        
        $offsets = $crop->getEntropyOffsets($image, 100, 100);
        
        $this->assertArrayHasKeys(['x', 'y'], $offsets);
        $this->assertIsInt($offsets['x']);
        $this->assertIsInt($offsets['y']);
        $this->assertGreaterThanOrEqual(0, $offsets['x']);
        $this->assertGreaterThanOrEqual(0, $offsets['y']);
    }
    
    public function testGetOffsetFromEntropy(): void
    {
        $image = $this->createTestImageWithPattern(200, 150);
        $crop = new TestCropEntropy();
        
        $offsets = $crop->getOffsetFromEntropy($image, 100, 100);
        
        $this->assertArrayHasKeys(['x', 'y'], $offsets);
        $this->assertIsInt($offsets['x']);
        $this->assertIsInt($offsets['y']);
    }
    
    public function testSliceHorizontal(): void
    {
        $image = $this->createTestImageWithPattern(200, 100);
        $crop = new TestCropEntropy();
        
        $result = $crop->slice($image, 200, 100, 'h');
        
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThanOrEqual(100, $result);
    }
    
    public function testSliceVertical(): void
    {
        $image = $this->createTestImageWithPattern(100, 200);
        $crop = new TestCropEntropy();
        
        $result = $crop->slice($image, 200, 100, 'v');
        
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThanOrEqual(100, $result);
    }
    
    public function testGetSafeZoneList(): void
    {
        $crop = new TestCropEntropy();
        $safeZones = $crop->getSafeZoneList();
        
        $this->assertIsArray($safeZones);
        $this->assertEmpty($safeZones); // Base implementation returns empty array
    }
    
    public function testGetPotential(): void
    {
        $crop = new TestCropEntropy();
        
        $potential = $crop->getPotential('top', 10, 5);
        
        $this->assertIsFloat($potential);
        $this->assertGreaterThanOrEqual(0, $potential);
    }
    
    public function testDifferentCroppingSizes(): void
    {
        $image = $this->createTestImageWithPattern(400, 300);
        $this->cropEntropy->setImage($image);
        
        $sizes = [
            [100, 100],
            [150, 100],
            [100, 150],
            [200, 200]
        ];
        
        foreach ($sizes as [$width, $height]) {
            $result = $this->cropEntropy->resizeAndCrop($width, $height);
            $this->assertImageDimensions($result, $width, $height);
        }
    }
}

/**
 * Test wrapper to expose protected methods
 */
class TestCropEntropy extends CropEntropy
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
    
    public function grayscaleEntropy(Imagick $image): float
    {
        return parent::grayscaleEntropy($image);
    }
    
    public function colorEntropy(Imagick $image): float
    {
        return parent::colorEntropy($image);
    }
    
    public function getEntropyOffsets(Imagick $original, int $targetWidth, int $targetHeight): array
    {
        return parent::getEntropyOffsets($original, $targetWidth, $targetHeight);
    }
    
    public function getOffsetFromEntropy(Imagick $originalImage, int $targetWidth, int $targetHeight): array
    {
        return parent::getOffsetFromEntropy($originalImage, $targetWidth, $targetHeight);
    }
    
    public function slice(Imagick $image, int $originalSize, int $targetSize, string $axis): int
    {
        return parent::slice($image, $originalSize, $targetSize, $axis);
    }
    
    public function getSafeZoneList(): array
    {
        return parent::getSafeZoneList();
    }
    
    public function getPotential(string $position, int $top, int $sliceSize): float
    {
        return parent::getPotential($position, $top, $sliceSize);
    }
}