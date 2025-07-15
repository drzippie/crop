<?php

namespace stojg\crop;

use stojg\crop\haar\HaarDetector;

/**
 * CropFace
 *
 * This class will try to find the most interesting point in the image by trying to find a face and
 * center the crop on that. Uses HAARPHP library for pure PHP face detection.
 *
 */
class CropFace extends CropEntropy
{
    const CLASSIFIER_FACE = '/haar/frontalface_default.php';
    const CLASSIFIER_PROFILE = '/haar/profileface.php';

    /**
     * imagePath original image path
     *
     * @var mixed
     * @access protected
     */
    protected $imagePath;

    /**
     * safeZoneList
     *
     * @var array
     * @access protected
     */
    protected $safeZoneList;

    /**
     * max execution time (in seconds)
     *
     * @var int
     * @access protected
     */
    protected $maxExecutionTime;

    /**
     *
     * @param string $imagePath
     */
    public function __construct($imagePath)
    {
        $this->imagePath = $imagePath;
        parent::__construct($imagePath);
    }

    /**
     * setMaxExecutionTime
     *
     * @param int $maxExecutionTime max execution time (in sec)
     * @access public
     * @return void
     */
    public function setMaxExecutionTime($maxExecutionTime)
    {
        $this->maxExecutionTime = $maxExecutionTime;
    }

    /**
     * getFaceList get faces positions and sizes
     *
     * @access protected
     * @return array
     */
    protected function getFaceList()
    {
        if (!extension_loaded('gd')) {
            $msg = 'GD extension must be installed for face detection.';
            throw new \Exception($msg);
        }

        if ($this->maxExecutionTime) {
            $timeBefore = microtime(true);
        }
        $faceList = $this->getFaceListFromClassifier(self::CLASSIFIER_FACE);
        if ($this->maxExecutionTime) {
            $timeSpent = microtime(true) - $timeBefore;
        }

        if (!$this->maxExecutionTime || $timeSpent < ($this->maxExecutionTime / 2)) {
            $profileList = $this->getFaceListFromClassifier(self::CLASSIFIER_PROFILE);
            $faceList = array_merge($faceList, $profileList);
        }

        return $faceList;
    }

    /**
     * getFaceListFromClassifier
     *
     * @param string $classifier
     * @access protected
     * @return array
     */
    protected function getFaceListFromClassifier($classifier)
    {
        $cascadeFile = __DIR__ . $classifier;
        
        if (!file_exists($cascadeFile)) {
            return array();
        }
        
        $cascadeData = include $cascadeFile;
        if (!$cascadeData) {
            return array();
        }
        
        // Load image using GD
        $gdImage = $this->loadGDImage($this->imagePath);
        if (!$gdImage) {
            return array();
        }
        
        // Create HaarDetector instance
        $detector = new HaarDetector($cascadeData);
        
        // Set image and detect faces
        $detector->image($gdImage);
        $detector->detect(1.0, 1.2, 0.1, 1, 0.2, true);
        
        $objects = $detector->getObjects();
        
        // Convert to expected format (x, y, w, h)
        $faceList = array();
        foreach ($objects as $object) {
            $faceList[] = array(
                'x' => $object['x'],
                'y' => $object['y'],
                'w' => $object['width'],
                'h' => $object['height']
            );
        }
        
        imagedestroy($gdImage);
        
        return $faceList;
    }
    
    /**
     * Load image using GD library
     *
     * @param string $imagePath
     * @return resource|false
     */
    protected function loadGDImage($imagePath)
    {
        if (!file_exists($imagePath)) {
            return false;
        }
        
        $info = getimagesize($imagePath);
        if (!$info) {
            return false;
        }
        
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($imagePath);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($imagePath);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($imagePath);
            default:
                return false;
        }
    }

    /**
     * getSafeZoneList
     *
     * @access private
     * @return array
     */
    protected function getSafeZoneList()
    {
        if (!isset($this->safeZoneList)) {
            $this->safeZoneList = array();
        }
        // the local key is the current image width-height
        $key = $this->originalImage->getImageWidth() . '-' . $this->originalImage->getImageHeight();

        if (!isset($this->safeZoneList[$key])) {
            $faceList = $this->getFaceList();

            // getFaceList works on the main image, so we use a ratio between main/current image
            $xRatio = $this->getBaseDimension('width') / $this->originalImage->getImageWidth();
            $yRatio = $this->getBaseDimension('height') / $this->originalImage->getImageHeight();

            $safeZoneList = array();
            foreach ($faceList as $face) {
                $hw = ceil($face['w'] / 2);
                $hh = ceil($face['h'] / 2);
                $safeZone = array(
                    'left' => $face['x'] - $hw,
                    'right' => $face['x'] + $face['w'] + $hw,
                    'top' => $face['y'] - $hh,
                    'bottom' => $face['y'] + $face['h'] + $hh
                );

                $safeZoneList[] = array(
                    'left' => round($safeZone['left'] / $xRatio),
                    'right' => round($safeZone['right'] / $xRatio),
                    'top' => round($safeZone['top'] / $yRatio),
                    'bottom' => round($safeZone['bottom'] / $yRatio),
                );
            }
            $this->safeZoneList[$key] = $safeZoneList;
        }

        return $this->safeZoneList[$key];
    }
}
