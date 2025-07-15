<?php

declare(strict_types=1);

namespace drzippie\crop;

use drzippie\crop\haar\HaarDetector;
use Imagick;
use Exception;

/**
 * CropFace
 *
 * This class will try to find the most interesting point in the image by trying to find a face and
 * center the crop on that. Uses HAARPHP library for pure PHP face detection.
 */
class CropFace extends CropEntropy
{
    public const CLASSIFIER_FACE = '/haar/frontalface_default.php';
    public const CLASSIFIER_PROFILE = '/haar/profileface.php';

    protected string $imagePath;
    protected array $safeZoneList = [];
    protected ?int $maxExecutionTime = null;

    public function __construct(string $imagePath)
    {
        $this->imagePath = $imagePath;
        parent::__construct($imagePath);
    }

    /**
     * Set max execution time
     */
    public function setMaxExecutionTime(int $maxExecutionTime): void
    {
        $this->maxExecutionTime = $maxExecutionTime;
    }

    /**
     * Get faces positions and sizes
     */
    protected function getFaceList(): array
    {
        if (!extension_loaded('gd')) {
            throw new Exception('GD extension must be installed for face detection.');
        }

        $timeBefore = $this->maxExecutionTime ? microtime(true) : null;
        $faceList = $this->getFaceListFromClassifier(self::CLASSIFIER_FACE);
        $timeSpent = $timeBefore ? microtime(true) - $timeBefore : 0;

        if (!$this->maxExecutionTime || $timeSpent < ($this->maxExecutionTime / 2)) {
            $profileList = $this->getFaceListFromClassifier(self::CLASSIFIER_PROFILE);
            $faceList = array_merge($faceList, $profileList);
        }

        return $faceList;
    }

    /**
     * Get face list from classifier
     */
    protected function getFaceListFromClassifier(string $classifier): array
    {
        $cascadeFile = __DIR__ . $classifier;
        
        if (!file_exists($cascadeFile)) {
            return [];
        }
        
        $cascadeData = include $cascadeFile;
        if (!$cascadeData) {
            return [];
        }
        
        // Load image using GD
        $gdImage = $this->loadGDImage($this->imagePath);
        if (!$gdImage) {
            return [];
        }
        
        // Create HaarDetector instance
        $detector = new HaarDetector($cascadeData);
        
        // Set image and detect faces
        $detector->image($gdImage);
        $detector->detect(1.0, 1.2, 0.1, 1, 0.2, true);
        
        $objects = $detector->getObjects();
        
        // Convert to expected format (x, y, w, h)
        $faceList = [];
        foreach ($objects as $object) {
            $faceList[] = [
                'x' => $object['x'],
                'y' => $object['y'],
                'w' => $object['width'],
                'h' => $object['height']
            ];
        }
        
        imagedestroy($gdImage);
        
        return $faceList;
    }
    
    /**
     * Load image using GD library
     */
    protected function loadGDImage(string $imagePath): \GdImage|false
    {
        if (!file_exists($imagePath)) {
            return false;
        }
        
        $info = getimagesize($imagePath);
        if (!$info) {
            return false;
        }
        
        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_GIF => imagecreatefromgif($imagePath),
            default => false
        };
    }

    /**
     * Get safe zone list
     */
    protected function getSafeZoneList(): array
    {
        if (!$this->originalImage) {
            return [];
        }
        
        // the local key is the current image width-height
        $key = $this->originalImage->getImageWidth() . '-' . $this->originalImage->getImageHeight();

        if (!isset($this->safeZoneList[$key])) {
            $faceList = $this->getFaceList();

            // getFaceList works on the main image, so we use a ratio between main/current image
            $xRatio = $this->getBaseDimension('width') / $this->originalImage->getImageWidth();
            $yRatio = $this->getBaseDimension('height') / $this->originalImage->getImageHeight();

            $safeZoneList = [];
            foreach ($faceList as $face) {
                $hw = ceil($face['w'] / 2);
                $hh = ceil($face['h'] / 2);
                $safeZone = [
                    'left' => $face['x'] - $hw,
                    'right' => $face['x'] + $face['w'] + $hw,
                    'top' => $face['y'] - $hh,
                    'bottom' => $face['y'] + $face['h'] + $hh
                ];

                $safeZoneList[] = [
                    'left' => round($safeZone['left'] / $xRatio),
                    'right' => round($safeZone['right'] / $xRatio),
                    'top' => round($safeZone['top'] / $yRatio),
                    'bottom' => round($safeZone['bottom'] / $yRatio),
                ];
            }
            $this->safeZoneList[$key] = $safeZoneList;
        }

        return $this->safeZoneList[$key];
    }
}