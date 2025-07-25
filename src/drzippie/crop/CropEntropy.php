<?php

declare(strict_types=1);

namespace drzippie\crop;

use Imagick;

/**
 * CropEntropy
 *
 * This class finds the a position in the picture with the most energy in it.
 *
 * Energy is in this case calculated by this:
 *
 * 1. Take the image and turn it into black and white
 * 2. Run a edge filter so that we're left with only edges.
 * 3. Find a piece in the picture that has the highest entropy (i.e. most edges)
 * 4. Return coordinates that makes sure that this piece of the picture is not cropped 'away'
 */
class CropEntropy extends Crop
{
    public const POTENTIAL_RATIO = 1.5;

    /**
     * Get special offset for class
     */
    protected function getSpecialOffset(Imagick $original, int $targetWidth, int $targetHeight): array
    {
        return $this->getEntropyOffsets($original, $targetWidth, $targetHeight);
    }

    /**
     * Get the topleftX and topleftY that will can be passed to a cropping method.
     */
    protected function getEntropyOffsets(Imagick $original, int $targetWidth, int $targetHeight): array
    {
        $measureImage = clone($original);
        // Enhance edges
        $measureImage->edgeimage(1);
        // Turn image into a grayscale
        $measureImage->modulateImage(100, 0, 100);
        // Turn everything darker than this to pitch black
        $measureImage->blackThresholdImage("#070707");
        // Get the calculated offset for cropping
        return $this->getOffsetFromEntropy($measureImage, $targetWidth, $targetHeight);
    }

    /**
     * Get the offset of where the crop should start
     */
    protected function getOffsetFromEntropy(Imagick $originalImage, int $targetWidth, int $targetHeight): array
    {
        // The entropy works better on a blured image
        $image = clone $originalImage;
        $image->blurImage(3, 2);

        $size = $image->getImageGeometry();

        $originalWidth = $size['width'];
        $originalHeight = $size['height'];

        $leftX = $this->slice($image, $originalWidth, $targetWidth, 'h');
        $topY = $this->slice($image, $originalHeight, $targetHeight, 'v');

        return ['x' => $leftX, 'y' => $topY];
    }

    /**
     * Slice image to find optimal crop position
     */
    protected function slice(Imagick $image, int $originalSize, int $targetSize, string $axis): int
    {
        $aSlice = null;
        $bSlice = null;

        // Just an arbitrary size of slice size
        $sliceSize = (int) ceil(($originalSize - $targetSize) / 25);

        $aBottom = $originalSize;
        $aTop = 0;

        // while there still are uninvestigated slices of the image
        while ($aBottom - $aTop > $targetSize) {
            // Make sure that we don't try to slice outside the picture
            $sliceSize = (int) min($aBottom - $aTop - $targetSize, $sliceSize);

            // Make a top slice image
            if (!$aSlice) {
                $aSlice = clone $image;
                if ($axis === 'h') {
                    $aSlice->cropImage($sliceSize, $originalSize, $aTop, 0);
                } else {
                    $aSlice->cropImage($originalSize, $sliceSize, 0, $aTop);
                }
            }

            // Make a bottom slice image
            if (!$bSlice) {
                $bSlice = clone $image;
                if ($axis === 'h') {
                    $bSlice->cropImage($sliceSize, $originalSize, $aBottom - $sliceSize, 0);
                } else {
                    $bSlice->cropImage($originalSize, $sliceSize, 0, $aBottom - $sliceSize);
                }
            }

            // calculate slices potential
            $aPosition = ($axis === 'h' ? 'left' : 'top');
            $bPosition = ($axis === 'h' ? 'right' : 'bottom');

            $aPot = $this->getPotential($aPosition, $aTop, $sliceSize);
            $bPot = $this->getPotential($bPosition, $aBottom, $sliceSize);

            $canCutA = ($aPot <= 0);
            $canCutB = ($bPot <= 0);

            // if no slices are "cutable", we force if a slice has a lot of potential
            if (!$canCutA && !$canCutB) {
                if ($aPot * self::POTENTIAL_RATIO < $bPot) {
                    $canCutA = true;
                } elseif ($aPot > $bPot * self::POTENTIAL_RATIO) {
                    $canCutB = true;
                }
            }

            // if we can only cut on one side
            if ($canCutA xor $canCutB) {
                if ($canCutA) {
                    $aTop += $sliceSize;
                    $aSlice = null;
                } else {
                    $aBottom -= $sliceSize;
                    $bSlice = null;
                }
            } elseif ($this->grayscaleEntropy($aSlice) < $this->grayscaleEntropy($bSlice)) {
                // bSlice has more entropy, so remove aSlice and bump aTop down
                $aTop += $sliceSize;
                $aSlice = null;
            } else {
                $aBottom -= $sliceSize;
                $bSlice = null;
            }
        }

        return $aTop;
    }

    /**
     * Get safe zone list
     */
    protected function getSafeZoneList(): array
    {
        return [];
    }

    /**
     * Get potential
     */
    protected function getPotential(string $position, int $top, int $sliceSize): float
    {
        $safeZoneList = $this->getSafeZoneList();

        $safeRatio = 0;

        if ($position === 'top' || $position === 'left') {
            $start = $top;
            $end = $top + $sliceSize;
        } else {
            $start = $top - $sliceSize;
            $end = $top;
        }

        for ($i = $start; $i < $end; $i++) {
            foreach ($safeZoneList as $safeZone) {
                if ($position === 'top' || $position === 'bottom') {
                    if ($safeZone['top'] <= $i && $safeZone['bottom'] >= $i) {
                        $safeRatio = max($safeRatio, ($safeZone['right'] - $safeZone['left']));
                    }
                } else {
                    if ($safeZone['left'] <= $i && $safeZone['right'] >= $i) {
                        $safeRatio = max($safeRatio, ($safeZone['bottom'] - $safeZone['top']));
                    }
                }
            }
        }

        return $safeRatio;
    }

    /**
     * Calculate the entropy for this image.
     *
     * A higher value of entropy means more noise / liveliness / color / business
     */
    protected function grayscaleEntropy(Imagick $image): float
    {
        // The histogram consists of a list of 0-254 and the number of pixels that has that value
        $histogram = $image->getImageHistogram();

        return $this->getEntropy($histogram, $this->area($image));
    }

    /**
     * Find out the entropy for a color image
     *
     * If the source image is in color we need to transform RGB into a grayscale image
     * so we can calculate the entropy more performant.
     */
    protected function colorEntropy(Imagick $image): float
    {
        $histogram = $image->getImageHistogram();
        $newHistogram = [];

        // Translates a color histogram into a bw histogram
        $histogramCount = count($histogram);
        for ($idx = 0; $idx < $histogramCount; $idx++) {
            $colorInfo = $histogram[$idx]->getColor();
            $grey = $this->rgb2bw($colorInfo['r'], $colorInfo['g'], $colorInfo['b']);
            if (!isset($newHistogram[$grey])) {
                $newHistogram[$grey] = $histogram[$idx]->getColorCount();
            } else {
                $newHistogram[$grey] += $histogram[$idx]->getColorCount();
            }
        }

        return $this->getEntropyFromArray($newHistogram, $this->area($image));
    }
}