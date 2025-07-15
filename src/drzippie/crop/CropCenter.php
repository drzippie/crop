<?php

declare(strict_types=1);

namespace drzippie\crop;

use Imagick;

/**
 * CropCenter
 *
 * The most basic of cropping techniques:
 *
 * 1. Find the exact center of the image
 * 2. Trim any edges that is bigger than the targetWidth and targetHeight
 */
class CropCenter extends Crop
{
    /**
     * Get special offset for class
     */
    protected function getSpecialOffset(Imagick $original, int $targetWidth, int $targetHeight): array
    {
        return $this->getCenterOffset($original, $targetWidth, $targetHeight);
    }

    /**
     * Get the cropping offset for the image based on the center of the image
     */
    protected function getCenterOffset(Imagick $image, int $targetWidth, int $targetHeight): array
    {
        $size = $image->getImageGeometry();
        $originalWidth = $size['width'];
        $originalHeight = $size['height'];
        $goalX = (int) (($originalWidth - $targetWidth) / 2);
        $goalY = (int) (($originalHeight - $targetHeight) / 2);

        return ['x' => $goalX, 'y' => $goalY];
    }
}