<?php

declare(strict_types=1);

namespace drzippie\crop\Tests;

use Imagick;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('ImageMagick extension is not available');
        }
        
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not available');
        }
        
        parent::setUp();
    }
    
    protected function createTestImage(int $width = 100, int $height = 100): Imagick
    {
        $image = new Imagick();
        $image->newImage($width, $height, 'white');
        $image->setImageFormat('png');
        return $image;
    }
    
    protected function createTestImageWithPattern(int $width = 100, int $height = 100): Imagick
    {
        $image = new Imagick();
        $image->newImage($width, $height, 'white');
        $image->setImageFormat('png');
        
        // Add some pattern to make entropy calculation meaningful
        $draw = new \ImagickDraw();
        $draw->setFillColor('black');
        $draw->rectangle(10, 10, 30, 30);
        $draw->setFillColor('red');
        $draw->rectangle(50, 50, 80, 80);
        $image->drawImage($draw);
        
        return $image;
    }
    
    protected function getTestImagePath(): string
    {
        return __DIR__ . '/fixtures/test.png';
    }
    
    protected function createTestImageFile(int $width = 100, int $height = 100): string
    {
        $image = $this->createTestImageWithPattern($width, $height);
        $path = tempnam(sys_get_temp_dir(), 'crop_test_');
        $image->writeImage($path);
        return $path;
    }
    
    protected function cleanupTestFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
    
    protected function assertImageDimensions(Imagick $image, int $expectedWidth, int $expectedHeight): void
    {
        $geometry = $image->getImageGeometry();
        $this->assertEquals($expectedWidth, $geometry['width'], 'Image width mismatch');
        $this->assertEquals($expectedHeight, $geometry['height'], 'Image height mismatch');
    }
    
    protected function assertArrayHasKeys(array $expectedKeys, array $array): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array, "Expected key '{$key}' not found in array");
        }
    }
}