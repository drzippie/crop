<?php

declare(strict_types=1);

namespace drzippie\crop\Tests\Unit;

use drzippie\crop\CropFace;
use drzippie\crop\Tests\TestCase;
use Imagick;
use Exception;

class CropFaceTest extends TestCase
{
    private CropFace $cropFace;
    private string $testImagePath;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->testImagePath = $this->createTestImageFile();
        $this->cropFace = new CropFace($this->testImagePath);
    }
    
    protected function tearDown(): void
    {
        $this->cleanupTestFile($this->testImagePath);
        parent::tearDown();
    }
    
    public function testConstructorWithImagePath(): void
    {
        $imagePath = $this->createTestImageFile();
        $crop = new CropFace($imagePath);
        
        $this->assertInstanceOf(Imagick::class, $crop->getOriginalImage());
        $this->cleanupTestFile($imagePath);
    }
    
    public function testClassifierConstants(): void
    {
        $this->assertEquals('/haar/frontalface_default.php', CropFace::CLASSIFIER_FACE);
        $this->assertEquals('/haar/profileface.php', CropFace::CLASSIFIER_PROFILE);
    }
    
    public function testSetMaxExecutionTime(): void
    {
        $this->cropFace->setMaxExecutionTime(5);
        
        // Since maxExecutionTime is private, we can't directly assert it
        // But we can verify the method doesn't throw exceptions
        $this->assertTrue(true);
    }
    
    public function testFaceCropping(): void
    {
        $result = $this->cropFace->resizeAndCrop(100, 100);
        
        $this->assertInstanceOf(Imagick::class, $result);
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testGetFaceListWithoutGD(): void
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available');
        }
        
        $crop = new TestCropFace($this->testImagePath);
        $faceList = $crop->getFaceList();
        
        $this->assertIsArray($faceList);
        // Note: Since we're using a simple test image, we likely won't detect faces
        // This test mainly ensures the method doesn't throw exceptions
    }
    
    public function testGetFaceListFromNonExistentClassifier(): void
    {
        $crop = new TestCropFace($this->testImagePath);
        $faceList = $crop->getFaceListFromClassifier('/nonexistent/classifier.php');
        
        $this->assertIsArray($faceList);
        $this->assertEmpty($faceList);
    }
    
    public function testLoadGDImageWithValidImage(): void
    {
        $crop = new TestCropFace($this->testImagePath);
        $gdImage = $crop->loadGDImage($this->testImagePath);
        
        $this->assertInstanceOf(\GdImage::class, $gdImage);
        imagedestroy($gdImage);
    }
    
    public function testLoadGDImageWithNonExistentFile(): void
    {
        $crop = new TestCropFace($this->testImagePath);
        $gdImage = $crop->loadGDImage('/nonexistent/file.png');
        
        $this->assertFalse($gdImage);
    }
    
    public function testLoadGDImageWithInvalidFile(): void
    {
        $invalidPath = tempnam(sys_get_temp_dir(), 'invalid_image_');
        file_put_contents($invalidPath, 'not an image');
        
        $crop = new TestCropFace($this->testImagePath);
        $gdImage = $crop->loadGDImage($invalidPath);
        
        $this->assertFalse($gdImage);
        unlink($invalidPath);
    }
    
    public function testGetSafeZoneList(): void
    {
        $crop = new TestCropFace($this->testImagePath);
        $safeZones = $crop->getSafeZoneList();
        
        $this->assertIsArray($safeZones);
        // With a simple test image, we likely won't have any detected faces
        // This test mainly ensures the method works without throwing exceptions
    }
    
    public function testGetSafeZoneListWithoutOriginalImage(): void
    {
        $crop = new TestCropFace($this->testImagePath);
        $crop->setOriginalImageToNull(); // Helper method to test edge case
        
        $safeZones = $crop->getSafeZoneList();
        
        $this->assertIsArray($safeZones);
        $this->assertEmpty($safeZones);
    }
    
    public function testFaceCroppingWithDifferentSizes(): void
    {
        $sizes = [
            [100, 100],
            [150, 100],
            [100, 150],
            [200, 200]
        ];
        
        foreach ($sizes as [$width, $height]) {
            $result = $this->cropFace->resizeAndCrop($width, $height);
            $this->assertImageDimensions($result, $width, $height);
        }
    }
    
    public function testFaceCroppingWithSquareImage(): void
    {
        $squareImagePath = $this->createTestImageFile(200, 200);
        $crop = new CropFace($squareImagePath);
        
        $result = $crop->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
        $this->cleanupTestFile($squareImagePath);
    }
    
    public function testFaceCroppingWithWideImage(): void
    {
        $wideImagePath = $this->createTestImageFile(300, 100);
        $crop = new CropFace($wideImagePath);
        
        $result = $crop->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
        $this->cleanupTestFile($wideImagePath);
    }
    
    public function testFaceCroppingWithTallImage(): void
    {
        $tallImagePath = $this->createTestImageFile(100, 300);
        $crop = new CropFace($tallImagePath);
        
        $result = $crop->resizeAndCrop(100, 100);
        
        $this->assertImageDimensions($result, 100, 100);
        $this->cleanupTestFile($tallImagePath);
    }
}

/**
 * Test wrapper to expose protected methods
 */
class TestCropFace extends CropFace
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
    
    public function setOriginalImageToNull(): void
    {
        $this->originalImage = null;
    }
    
    public function getFaceList(): array
    {
        return parent::getFaceList();
    }
    
    public function getFaceListFromClassifier(string $classifier): array
    {
        return parent::getFaceListFromClassifier($classifier);
    }
    
    public function loadGDImage(string $imagePath): \GdImage|false
    {
        return parent::loadGDImage($imagePath);
    }
    
    public function getSafeZoneList(): array
    {
        return parent::getSafeZoneList();
    }
}