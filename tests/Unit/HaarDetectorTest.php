<?php

declare(strict_types=1);

namespace drzippie\crop\Tests\Unit;

use drzippie\crop\haar\HaarDetector;
use drzippie\crop\Tests\TestCase;

class HaarDetectorTest extends TestCase
{
    private array $mockCascadeData;
    private HaarDetector $detector;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a minimal mock cascade data structure
        $this->mockCascadeData = [
            'size' => [24, 24],
            'stages' => [
                [
                    'threshold' => -1.0,
                    'features' => [
                        [
                            'threshold' => 0.5,
                            'left_val' => 1.0,
                            'right_val' => -1.0,
                            'rectangles' => [
                                [6, 4, 12, 9, -1.0],
                                [6, 7, 12, 3, 3.0]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $this->detector = new HaarDetector($this->mockCascadeData);
    }
    
    public function testConstructorWithCascadeData(): void
    {
        $detector = new HaarDetector($this->mockCascadeData);
        
        $this->assertInstanceOf(HaarDetector::class, $detector);
        $this->assertEmpty($detector->getObjects());
    }
    
    public function testConstructorWithoutCascadeData(): void
    {
        $detector = new HaarDetector();
        
        $this->assertInstanceOf(HaarDetector::class, $detector);
        $this->assertEmpty($detector->getObjects());
    }
    
    public function testImageWithGDResource(): void
    {
        $gdImage = $this->createGDImage(100, 100);
        
        $result = $this->detector->image($gdImage);
        
        $this->assertSame($this->detector, $result);
        imagedestroy($gdImage);
    }
    
    public function testImageWithScale(): void
    {
        $gdImage = $this->createGDImage(100, 100);
        
        $result = $this->detector->image($gdImage, 0.5);
        
        $this->assertSame($this->detector, $result);
        imagedestroy($gdImage);
    }
    
    public function testDetectWithoutImage(): void
    {
        $result = $this->detector->detect();
        
        $this->assertSame($this->detector, $result);
        $this->assertEmpty($this->detector->getObjects());
    }
    
    public function testDetectWithImage(): void
    {
        $gdImage = $this->createGDImage(100, 100);
        
        $this->detector->image($gdImage);
        $result = $this->detector->detect();
        
        $this->assertSame($this->detector, $result);
        $this->assertIsArray($this->detector->getObjects());
        
        imagedestroy($gdImage);
    }
    
    public function testDetectWithParameters(): void
    {
        $gdImage = $this->createGDImage(100, 100);
        
        $this->detector->image($gdImage);
        $result = $this->detector->detect(1.0, 1.2, 0.1, 1, 0.2, true);
        
        $this->assertSame($this->detector, $result);
        $this->assertIsArray($this->detector->getObjects());
        
        imagedestroy($gdImage);
    }
    
    public function testSelection(): void
    {
        $result = $this->detector->selection(10, 10, 50, 50);
        
        $this->assertSame($this->detector, $result);
    }
    
    public function testCascade(): void
    {
        $newCascadeData = [
            'size' => [20, 20],
            'stages' => []
        ];
        
        $result = $this->detector->cascade($newCascadeData);
        
        $this->assertSame($this->detector, $result);
    }
    
    public function testGetObjectsInitially(): void
    {
        $objects = $this->detector->getObjects();
        
        $this->assertIsArray($objects);
        $this->assertEmpty($objects);
    }
    
    public function testGetObjectsAfterDetection(): void
    {
        $gdImage = $this->createGDImage(100, 100);
        
        $this->detector->image($gdImage);
        $this->detector->detect();
        $objects = $this->detector->getObjects();
        
        $this->assertIsArray($objects);
        // Objects array might be empty if no faces detected, but should be an array
        
        imagedestroy($gdImage);
    }
    
    public function testFluentInterface(): void
    {
        $gdImage = $this->createGDImage(100, 100);
        
        $result = $this->detector
            ->image($gdImage)
            ->selection(0, 0, 100, 100)
            ->cascade($this->mockCascadeData)
            ->detect();
        
        $this->assertSame($this->detector, $result);
        imagedestroy($gdImage);
    }
    
    public function testDetectionWithSmallImage(): void
    {
        $gdImage = $this->createGDImage(20, 20);
        
        $this->detector->image($gdImage);
        $this->detector->detect();
        
        $objects = $this->detector->getObjects();
        $this->assertIsArray($objects);
        
        imagedestroy($gdImage);
    }
    
    public function testDetectionWithLargeImage(): void
    {
        $gdImage = $this->createGDImage(200, 200);
        
        $this->detector->image($gdImage);
        $this->detector->detect();
        
        $objects = $this->detector->getObjects();
        $this->assertIsArray($objects);
        
        imagedestroy($gdImage);
    }
    
    public function testDetectionWithDifferentScales(): void
    {
        $gdImage = $this->createGDImage(100, 100);
        
        $scales = [0.5, 1.0, 1.5, 2.0];
        
        foreach ($scales as $scale) {
            $this->detector->image($gdImage, $scale);
            $this->detector->detect();
            
            $objects = $this->detector->getObjects();
            $this->assertIsArray($objects);
        }
        
        imagedestroy($gdImage);
    }
    
    public function testDetectionWithDifferentParameters(): void
    {
        $gdImage = $this->createGDImage(100, 100);
        $this->detector->image($gdImage);
        
        $parameters = [
            [1.0, 1.1, 0.05, 1, 0.1, true],
            [1.0, 1.2, 0.1, 2, 0.2, false],
            [1.0, 1.3, 0.15, 3, 0.3, true]
        ];
        
        foreach ($parameters as $params) {
            $this->detector->detect(...$params);
            
            $objects = $this->detector->getObjects();
            $this->assertIsArray($objects);
        }
        
        imagedestroy($gdImage);
    }
    
    private function createGDImage(int $width, int $height): \GdImage
    {
        $image = imagecreate($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fill with white background
        imagefill($image, 0, 0, $white);
        
        // Add some simple pattern
        imagerectangle($image, 10, 10, 30, 30, $black);
        
        return $image;
    }
}