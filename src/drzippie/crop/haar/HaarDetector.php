<?php

declare(strict_types=1);

namespace drzippie\crop\haar;

/**
 * HaarDetector - Feature Detection Library for PHP
 * 
 * @author Nikos M. (http://nikos-web-development.netai.net/)
 * @version 1.0.0
 * @license MIT
 * 
 * Adapted from HAARPHP (https://github.com/foo123/HAARPHP)
 * Original algorithm based on Viola-Jones Object Detection
 */
class HaarDetector
{
    private $haardata;
    private $objects;
    private $canvas;
    private $selection;
    private $baseScale = 1.0;
    private $scale = 1.2;
    private $increment = 0.1;
    private $neighbors = 1;
    private $epsilon = 0.2;
    private $doCannyPruning = true;
    
    public function __construct($haardata = null)
    {
        $this->haardata = $haardata;
        $this->objects = array();
        $this->canvas = array('width' => 0, 'height' => 0);
        $this->selection = array('x' => 0, 'y' => 0, 'width' => 0, 'height' => 0);
    }
    
    public function image($img, $scale = 1.0)
    {
        if (is_resource($img)) {
            $this->canvas['width'] = imagesx($img);
            $this->canvas['height'] = imagesy($img);
            
            $this->selection['width'] = $this->canvas['width'];
            $this->selection['height'] = $this->canvas['height'];
            
            $this->canvas['data'] = $this->imageToGrayscale($img);
            $this->canvas['integral'] = $this->integralImage($this->canvas['data'], $this->canvas['width'], $this->canvas['height']);
            $this->canvas['squares'] = $this->integralSquares($this->canvas['data'], $this->canvas['width'], $this->canvas['height']);
            
            if ($scale != 1.0) {
                $this->scaleImage($scale);
            }
        }
        
        return $this;
    }
    
    public function detect($baseScale = 1.0, $scale = 1.2, $increment = 0.1, $neighbors = 1, $epsilon = 0.2, $doCannyPruning = true)
    {
        $this->baseScale = $baseScale;
        $this->scale = $scale;
        $this->increment = $increment;
        $this->neighbors = $neighbors;
        $this->epsilon = $epsilon;
        $this->doCannyPruning = $doCannyPruning;
        
        $this->objects = array();
        
        if (!$this->haardata || !isset($this->canvas['data'])) {
            return $this;
        }
        
        $width = $this->canvas['width'];
        $height = $this->canvas['height'];
        
        $baseWidth = $this->haardata['size'][0];
        $baseHeight = $this->haardata['size'][1];
        
        $currentScale = $this->baseScale;
        
        while ($currentScale * $baseWidth < $width && $currentScale * $baseHeight < $height) {
            $scaledWidth = floor($baseWidth * $currentScale);
            $scaledHeight = floor($baseHeight * $currentScale);
            
            $step = floor($currentScale * 2);
            
            for ($y = 0; $y <= $height - $scaledHeight; $y += $step) {
                for ($x = 0; $x <= $width - $scaledWidth; $x += $step) {
                    if ($this->detectFeature($x, $y, $currentScale)) {
                        $this->objects[] = array(
                            'x' => $x,
                            'y' => $y,
                            'width' => $scaledWidth,
                            'height' => $scaledHeight
                        );
                    }
                }
            }
            
            $currentScale *= $this->scale;
        }
        
        if (!empty($this->objects)) {
            $this->objects = $this->groupRectangles($this->objects, $this->neighbors, $this->epsilon);
        }
        
        return $this;
    }
    
    public function selection($x, $y, $width, $height)
    {
        $this->selection = array('x' => $x, 'y' => $y, 'width' => $width, 'height' => $height);
        return $this;
    }
    
    public function cascade($haardata)
    {
        $this->haardata = $haardata;
        return $this;
    }
    
    public function getObjects()
    {
        return $this->objects;
    }
    
    private function imageToGrayscale($img)
    {
        $width = imagesx($img);
        $height = imagesy($img);
        $data = array();
        
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                
                $gray = ($r * 0.299 + $g * 0.587 + $b * 0.114);
                $data[$y * $width + $x] = $gray;
            }
        }
        
        return $data;
    }
    
    private function integralImage($data, $width, $height)
    {
        $integral = array_fill(0, $width * $height, 0);
        
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $idx = $y * $width + $x;
                
                $integral[$idx] = $data[$idx];
                
                if ($x > 0) {
                    $integral[$idx] += $integral[$idx - 1];
                }
                
                if ($y > 0) {
                    $integral[$idx] += $integral[($y - 1) * $width + $x];
                }
                
                if ($x > 0 && $y > 0) {
                    $integral[$idx] -= $integral[($y - 1) * $width + ($x - 1)];
                }
            }
        }
        
        return $integral;
    }
    
    private function integralSquares($data, $width, $height)
    {
        $squares = array_fill(0, $width * $height, 0);
        
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $idx = $y * $width + $x;
                $value = $data[$idx];
                
                $squares[$idx] = $value * $value;
                
                if ($x > 0) {
                    $squares[$idx] += $squares[$idx - 1];
                }
                
                if ($y > 0) {
                    $squares[$idx] += $squares[($y - 1) * $width + $x];
                }
                
                if ($x > 0 && $y > 0) {
                    $squares[$idx] -= $squares[($y - 1) * $width + ($x - 1)];
                }
            }
        }
        
        return $squares;
    }
    
    private function scaleImage($scale)
    {
        $newWidth = floor($this->canvas['width'] * $scale);
        $newHeight = floor($this->canvas['height'] * $scale);
        
        $newData = array_fill(0, $newWidth * $newHeight, 0);
        
        for ($y = 0; $y < $newHeight; $y++) {
            for ($x = 0; $x < $newWidth; $x++) {
                $srcX = floor($x / $scale);
                $srcY = floor($y / $scale);
                
                if ($srcX < $this->canvas['width'] && $srcY < $this->canvas['height']) {
                    $newData[$y * $newWidth + $x] = $this->canvas['data'][$srcY * $this->canvas['width'] + $srcX];
                }
            }
        }
        
        $this->canvas['width'] = $newWidth;
        $this->canvas['height'] = $newHeight;
        $this->canvas['data'] = $newData;
        $this->canvas['integral'] = $this->integralImage($newData, $newWidth, $newHeight);
        $this->canvas['squares'] = $this->integralSquares($newData, $newWidth, $newHeight);
    }
    
    private function detectFeature($x, $y, $scale)
    {
        if (!isset($this->haardata['stages'])) {
            return false;
        }
        
        $stages = $this->haardata['stages'];
        $baseWidth = $this->haardata['size'][0];
        $baseHeight = $this->haardata['size'][1];
        
        $mean = $this->calculateMean($x, $y, $baseWidth * $scale, $baseHeight * $scale);
        $variance = $this->calculateVariance($x, $y, $baseWidth * $scale, $baseHeight * $scale, $mean);
        
        if ($variance <= 0) {
            return false;
        }
        
        $stddev = sqrt($variance);
        $threshold = $stddev * 0.5;
        
        foreach ($stages as $stage) {
            $stageSum = 0;
            $stageThreshold = $stage['threshold'];
            
            foreach ($stage['features'] as $feature) {
                $featureSum = 0;
                
                foreach ($feature['rectangles'] as $rect) {
                    $rectSum = $this->calculateRectSum(
                        $x + $rect[0] * $scale,
                        $y + $rect[1] * $scale,
                        $rect[2] * $scale,
                        $rect[3] * $scale
                    );
                    
                    $featureSum += $rectSum * $rect[4];
                }
                
                $featureSum /= $threshold;
                
                if ($featureSum < $feature['threshold']) {
                    $stageSum += $feature['left_val'];
                } else {
                    $stageSum += $feature['right_val'];
                }
            }
            
            if ($stageSum < $stageThreshold) {
                return false;
            }
        }
        
        return true;
    }
    
    private function calculateMean($x, $y, $width, $height)
    {
        $sum = $this->calculateRectSum($x, $y, $width, $height);
        return $sum / ($width * $height);
    }
    
    private function calculateVariance($x, $y, $width, $height, $mean)
    {
        $squareSum = $this->calculateRectSquareSum($x, $y, $width, $height);
        $area = $width * $height;
        return ($squareSum / $area) - ($mean * $mean);
    }
    
    private function calculateRectSum($x, $y, $width, $height)
    {
        $canvasWidth = $this->canvas['width'];
        $integral = $this->canvas['integral'];
        
        $x1 = max(0, min($x, $canvasWidth - 1));
        $y1 = max(0, min($y, $this->canvas['height'] - 1));
        $x2 = max(0, min($x + $width - 1, $canvasWidth - 1));
        $y2 = max(0, min($y + $height - 1, $this->canvas['height'] - 1));
        
        $sum = $integral[$y2 * $canvasWidth + $x2];
        
        if ($x1 > 0) {
            $sum -= $integral[$y2 * $canvasWidth + ($x1 - 1)];
        }
        
        if ($y1 > 0) {
            $sum -= $integral[($y1 - 1) * $canvasWidth + $x2];
        }
        
        if ($x1 > 0 && $y1 > 0) {
            $sum += $integral[($y1 - 1) * $canvasWidth + ($x1 - 1)];
        }
        
        return $sum;
    }
    
    private function calculateRectSquareSum($x, $y, $width, $height)
    {
        $canvasWidth = $this->canvas['width'];
        $squares = $this->canvas['squares'];
        
        $x1 = max(0, min($x, $canvasWidth - 1));
        $y1 = max(0, min($y, $this->canvas['height'] - 1));
        $x2 = max(0, min($x + $width - 1, $canvasWidth - 1));
        $y2 = max(0, min($y + $height - 1, $this->canvas['height'] - 1));
        
        $sum = $squares[$y2 * $canvasWidth + $x2];
        
        if ($x1 > 0) {
            $sum -= $squares[$y2 * $canvasWidth + ($x1 - 1)];
        }
        
        if ($y1 > 0) {
            $sum -= $squares[($y1 - 1) * $canvasWidth + $x2];
        }
        
        if ($x1 > 0 && $y1 > 0) {
            $sum += $squares[($y1 - 1) * $canvasWidth + ($x1 - 1)];
        }
        
        return $sum;
    }
    
    private function groupRectangles($rectangles, $neighbors, $epsilon)
    {
        if (empty($rectangles)) {
            return array();
        }
        
        $groups = array();
        $visited = array_fill(0, count($rectangles), false);
        
        for ($i = 0; $i < count($rectangles); $i++) {
            if ($visited[$i]) {
                continue;
            }
            
            $group = array($rectangles[$i]);
            $visited[$i] = true;
            
            for ($j = $i + 1; $j < count($rectangles); $j++) {
                if ($visited[$j]) {
                    continue;
                }
                
                if ($this->isRectangleClose($rectangles[$i], $rectangles[$j], $epsilon)) {
                    $group[] = $rectangles[$j];
                    $visited[$j] = true;
                }
            }
            
            if (count($group) >= $neighbors) {
                $groups[] = $this->mergeRectangles($group);
            }
        }
        
        return $groups;
    }
    
    private function isRectangleClose($rect1, $rect2, $epsilon)
    {
        $dx = abs($rect1['x'] - $rect2['x']);
        $dy = abs($rect1['y'] - $rect2['y']);
        $dw = abs($rect1['width'] - $rect2['width']);
        $dh = abs($rect1['height'] - $rect2['height']);
        
        $avgWidth = ($rect1['width'] + $rect2['width']) / 2;
        $avgHeight = ($rect1['height'] + $rect2['height']) / 2;
        
        return ($dx <= $epsilon * $avgWidth) && 
               ($dy <= $epsilon * $avgHeight) && 
               ($dw <= $epsilon * $avgWidth) && 
               ($dh <= $epsilon * $avgHeight);
    }
    
    private function mergeRectangles($rectangles)
    {
        $x = 0;
        $y = 0;
        $width = 0;
        $height = 0;
        
        foreach ($rectangles as $rect) {
            $x += $rect['x'];
            $y += $rect['y'];
            $width += $rect['width'];
            $height += $rect['height'];
        }
        
        $count = count($rectangles);
        
        return array(
            'x' => floor($x / $count),
            'y' => floor($y / $count),
            'width' => floor($width / $count),
            'height' => floor($height / $count)
        );
    }
}