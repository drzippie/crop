<?php

declare(strict_types=1);

namespace drzippie\crop;

use Imagick;
use Exception;

/**
 * CropBalanced
 *
 * This class calculates the most interesting point in the image by:
 *
 * 1. Dividing the image into four equally squares
 * 2. Find the most energetic point per square
 * 3. Finding the images weighted mean interest point
 */
class CropBalanced extends Crop
{
    /**
     * Get special offset for class
     */
    protected function getSpecialOffset(Imagick $original, int $targetWidth, int $targetHeight): array
    {
        return $this->getRandomEdgeOffset($original, $targetWidth, $targetHeight);
    }

    protected function getRandomEdgeOffset(Imagick $original, int $targetWidth, int $targetHeight): array
    {
        $measureImage = clone($original);
        // Enhance edges with radius 1
        $measureImage->edgeimage(1);
        // Turn image into a grayscale
        $measureImage->modulateImage(100, 0, 100);
        // Turn everything darker than this to pitch black
        $measureImage->blackThresholdImage("#070707");
        // Get the calculated offset for cropping
        return $this->getOffsetBalancedForImage($measureImage, $targetWidth, $targetHeight);
    }

    public function getOffsetBalanced(int $targetWidth, int $targetHeight): array
    {
        if (!$this->originalImage) {
            throw new Exception('No original image set');
        }
        return $this->getOffsetBalancedForImage($this->originalImage, $targetWidth, $targetHeight);
    }

    private function getOffsetBalancedForImage(Imagick $image, int $targetWidth, int $targetHeight): array
    {
        $size = $image->getImageGeometry();

        $points = [];

        $halfWidth = (int) ceil($size['width'] / 2);
        $halfHeight = (int) ceil($size['height'] / 2);

        // First quadrant
        $clone = clone($image);
        $clone->cropimage($halfWidth, $halfHeight, 0, 0);
        $point = $this->getHighestEnergyPoint($clone);
        $points[] = ['x' => $point['x'], 'y' => $point['y'], 'sum' => $point['sum']];

        // Second quadrant
        $clone = clone($image);
        $clone->cropimage($halfWidth, $halfHeight, $halfWidth, 0);
        $point = $this->getHighestEnergyPoint($clone);
        $points[] = ['x' => $point['x'] + $halfWidth, 'y' => $point['y'], 'sum' => $point['sum']];

        // Third quadrant
        $clone = clone($image);
        $clone->cropimage($halfWidth, $halfHeight, 0, $halfHeight);
        $point = $this->getHighestEnergyPoint($clone);
        $points[] = ['x' => $point['x'], 'y' => $point['y'] + $halfHeight, 'sum' => $point['sum']];

        // Fourth quadrant
        $clone = clone($image);
        $clone->cropimage($halfWidth, $halfHeight, $halfWidth, $halfHeight);
        $point = $this->getHighestEnergyPoint($clone);
        $points[] = ['x' => $point['x'] + $halfWidth, 'y' => $point['y'] + $halfHeight, 'sum' => $point['sum']];

        // get the total sum value so we can find out a mean center point
        $totalWeight = array_reduce(
            $points,
            fn($result, $array) => $result + $array['sum'],
            0
        );

        $centerX = 0;
        $centerY = 0;

        // If we found a center point, made the calculations to found the coords
        if ($totalWeight) {
            // Calculate the mean weighted center x and y
            $totalPoints = count($points);
            for ($idx = 0; $idx < $totalPoints; $idx++) {
                $centerX += $points[$idx]['x'] * ($points[$idx]['sum'] / $totalWeight);
                $centerY += $points[$idx]['y'] * ($points[$idx]['sum'] / $totalWeight);
            }
        }

        // From the weighted center point to the topleft corner of the crop would be
        $topleftX = (int) max(0, ($centerX - $targetWidth / 2));
        $topleftY = (int) max(0, ($centerY - $targetHeight / 2));

        // If we don't have enough width for the crop, back up $topleftX until
        // we can make the image meet $targetWidth
        if ($topleftX + $targetWidth > $size['width']) {
            $topleftX -= ($topleftX + $targetWidth) - $size['width'];
        }
        // If we don't have enough height for the crop, back up $topleftY until
        // we can make the image meet $targetHeight
        if ($topleftY + $targetHeight > $size['height']) {
            $topleftY -= ($topleftY + $targetHeight) - $size['height'];
        }

        return ['x' => $topleftX, 'y' => $topleftY];
    }

    /**
     * By doing random sampling from the image, find the most energetic point on the passed in image
     */
    protected function getHighestEnergyPoint(Imagick $image): array
    {
        $size = $image->getImageGeometry();
        // It's more performant doing random pixel lookup via GD
        $im = imagecreatefromstring($image->getImageBlob());
        if ($im === false) {
            throw new Exception('GD failed to create image from string');
        }
        
        $xcenter = 0;
        $ycenter = 0;
        $sum = 0;
        // Only sample 1/50 of all the pixels in the image
        $sampleSize = (int) (round($size['height'] * $size['width']) / 50);

        for ($k = 0; $k < $sampleSize; $k++) {
            $i = mt_rand(0, $size['width'] - 1);
            $j = mt_rand(0, $size['height'] - 1);

            $rgb = imagecolorat($im, $i, $j);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $val = $this->rgb2bw($r, $g, $b);
            $sum += $val;
            $xcenter += ($i + 1) * $val;
            $ycenter += ($j + 1) * $val;
        }

        if ($sum) {
            $xcenter /= $sum;
            $ycenter /= $sum;
        }

        $point = [
            'x' => $xcenter, 
            'y' => $ycenter, 
            'sum' => $sum / round($size['height'] * $size['width'])
        ];

        imagedestroy($im);
        return $point;
    }
}