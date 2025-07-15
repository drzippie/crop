<?php

declare(strict_types=1);

namespace drzippie\crop\Tests\Unit;

use drzippie\crop\Crop;
use drzippie\crop\Tests\TestCase;
use Imagick;
use RuntimeException;

class CropTest extends TestCase
{
    private TestCrop $crop;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->crop = new TestCrop();
    }
    
    public function testConstructorWithImagePath(): void
    {
        $imagePath = $this->createTestImageFile();
        $crop = new TestCrop($imagePath);
        
        $this->assertInstanceOf(Imagick::class, $crop->getOriginalImage());
        $this->cleanupTestFile($imagePath);
    }
    
    public function testConstructorWithImagickObject(): void
    {
        $image = $this->createTestImage();
        $crop = new TestCrop($image);
        
        $this->assertSame($image, $crop->getOriginalImage());
    }
    
    public function testConstructorWithNull(): void
    {
        $crop = new TestCrop(null);
        
        $this->assertNull($crop->getOriginalImage());
    }
    
    public function testSetImage(): void
    {
        $image = $this->createTestImage(200, 150);
        $result = $this->crop->setImage($image);
        
        $this->assertSame($this->crop, $result);
        $this->assertSame($image, $this->crop->getOriginalImage());
        $this->assertEquals(200, $this->crop->getBaseDimension('width'));
        $this->assertEquals(150, $this->crop->getBaseDimension('height'));
    }
    
    public function testGetSetFilter(): void
    {
        $this->assertEquals(Imagick::FILTER_CUBIC, $this->crop->getFilter());
        
        $result = $this->crop->setFilter(Imagick::FILTER_LANCZOS);
        $this->assertSame($this->crop, $result);
        $this->assertEquals(Imagick::FILTER_LANCZOS, $this->crop->getFilter());
    }
    
    public function testGetSetBlur(): void
    {
        $this->assertEquals(0.5, $this->crop->getBlur());
        
        $result = $this->crop->setBlur(1.0);
        $this->assertSame($this->crop, $result);
        $this->assertEquals(1.0, $this->crop->getBlur());
    }
    
    public function testGetSetAutoOrient(): void
    {
        $this->assertTrue($this->crop->getAutoOrient());
        
        $result = $this->crop->setAutoOrient(false);
        $this->assertSame($this->crop, $result);
        $this->assertFalse($this->crop->getAutoOrient());
    }
    
    public function testResizeAndCropThrowsExceptionWithoutImage(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No image set');
        
        $this->crop->resizeAndCrop(100, 100);
    }
    
    public function testResizeAndCrop(): void
    {
        $image = $this->createTestImage(200, 150);
        $this->crop->setImage($image);
        
        $result = $this->crop->resizeAndCrop(100, 100);
        
        $this->assertInstanceOf(Imagick::class, $result);
        $this->assertImageDimensions($result, 100, 100);
    }
    
    public function testGetSafeResizeOffset(): void
    {
        $image = $this->createTestImage(200, 150);
        $offset = $this->crop->getSafeResizeOffset($image, 100, 100);
        
        $this->assertArrayHasKeys(['width', 'height'], $offset);
        $this->assertIsInt($offset['width']);
        $this->assertIsInt($offset['height']);
        $this->assertTrue($offset['width'] >= 100);
        $this->assertTrue($offset['height'] >= 100);
    }
    
    public function testRgb2bw(): void
    {
        $result = $this->crop->rgb2bw(255, 255, 255); // White
        $this->assertEquals(255.0, $result);
        
        $result = $this->crop->rgb2bw(0, 0, 0); // Black
        $this->assertEquals(0.0, $result);
        
        $result = $this->crop->rgb2bw(255, 0, 0); // Red
        $this->assertEqualsWithDelta(76.245, $result, 0.001);
    }
    
    public function testArea(): void
    {
        $image = $this->createTestImage(100, 50);
        $area = $this->crop->area($image);
        
        $this->assertEquals(5000, $area);
    }
    
    public function testProfiling(): void
    {
        Crop::start();
        usleep(1000); // 1ms
        $mark = Crop::mark();
        
        $this->assertIsString($mark);
        $this->assertStringContainsString('ms', $mark);
    }
    
    public function testGetBaseDimensionWithoutImage(): void
    {
        $this->assertEquals(0, $this->crop->getBaseDimension('width'));
        $this->assertEquals(0, $this->crop->getBaseDimension('height'));
        $this->assertEquals(0, $this->crop->getBaseDimension('invalid'));
    }
    
    public function testGetBaseDimensionWithImage(): void
    {
        $image = $this->createTestImage(200, 150);
        $this->crop->setImage($image);
        
        $this->assertEquals(200, $this->crop->getBaseDimension('width'));
        $this->assertEquals(150, $this->crop->getBaseDimension('height'));
    }
}

/**
 * Test implementation of abstract Crop class
 */
class TestCrop extends Crop
{
    protected function getSpecialOffset(Imagick $original, int $targetWidth, int $targetHeight): array
    {
        return ['x' => 0, 'y' => 0];
    }
    
    // Expose protected methods for testing
    public function getOriginalImage(): ?Imagick
    {
        return $this->originalImage;
    }
    
    public function getSafeResizeOffset(Imagick $image, int $targetWidth, int $targetHeight): array
    {
        return parent::getSafeResizeOffset($image, $targetWidth, $targetHeight);
    }
    
    public function rgb2bw(int $r, int $g, int $b): float
    {
        return parent::rgb2bw($r, $g, $b);
    }
    
    public function area(Imagick $image): int
    {
        return parent::area($image);
    }
    
    public function getBaseDimension(string $key): int
    {
        return parent::getBaseDimension($key);
    }
}