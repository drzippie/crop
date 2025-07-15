<?php

declare(strict_types=1);

namespace drzippie\crop;

use Imagick;

/**
 * Base class for all Croppers
 */
abstract class Crop
{
    protected static float $start_time = 0.0;
    
    protected ?Imagick $originalImage = null;
    
    protected int $filter = Imagick::FILTER_CUBIC;
    
    protected float $blur = 0.5;
    
    protected bool $autoOrient = true;
    
    protected ?array $baseDimension = null;

    /**
     * Profiling method
     */
    public static function start(): void
    {
        self::$start_time = microtime(true);
    }

    /**
     * Profiling method
     */
    public static function mark(): string
    {
        $end_time = (microtime(true) - self::$start_time) * 1000;
        return sprintf("%.1fms", $end_time);
    }

    /**
     * Constructor
     */
    public function __construct(string|Imagick|null $imagePath = null)
    {
        if ($imagePath) {
            if (is_string($imagePath)) {
                $this->setImage(new Imagick($imagePath));
            } else {
                $this->setImage($imagePath);
            }
        }
    }

    /**
     * Sets the object Image to be cropped
     */
    public function setImage(Imagick $image): self
    {
        $this->originalImage = $image;

        // set base image dimensions
        $this->setBaseDimensions(
            $this->originalImage->getImageWidth(),
            $this->originalImage->getImageHeight()
        );
        return $this;
    }

    /**
     * Get the area in pixels for this image
     */
    protected function area(Imagick $image): int
    {
        $size = $image->getImageGeometry();
        return $size['height'] * $size['width'];
    }

    /**
     * Get the filter value to use for resizeImage call
     */
    public function getFilter(): int
    {
        return $this->filter;
    }

    /**
     * Set the filter value to use for resizeImage call
     */
    public function setFilter(int $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Get the blur value to use for resizeImage call
     */
    public function getBlur(): float
    {
        return $this->blur;
    }

    /**
     * Set the blur value to use for resizeImage call
     */
    public function setBlur(float $blur): self
    {
        $this->blur = $blur;
        return $this;
    }

    /**
     * Get whether image data should be rotated according to EXIF metadata
     */
    public function getAutoOrient(): bool
    {
        return $this->autoOrient;
    }

    /**
     * Set whether image data should be rotated according to EXIF metadata
     */
    public function setAutoOrient(bool $autoOrient): self
    {
        $this->autoOrient = $autoOrient;
        return $this;
    }

    /**
     * Resize and crop the image so it dimensions matches $targetWidth and $targetHeight
     */
    public function resizeAndCrop(int $targetWidth, int $targetHeight): Imagick
    {
        if (!$this->originalImage) {
            throw new \RuntimeException('No image set');
        }
        
        if ($this->getAutoOrient()) {
            $this->autoOrient();
        }

        // First get the size that we can use to safely trim down the image without cropping any sides
        $crop = $this->getSafeResizeOffset($this->originalImage, $targetWidth, $targetHeight);
        // Resize the image
        $this->originalImage->resizeImage($crop['width'], $crop['height'], $this->getFilter(), $this->getBlur());
        // Get the offset for cropping the image further
        $offset = $this->getSpecialOffset($this->originalImage, $targetWidth, $targetHeight);
        // Crop the image
        $this->originalImage->cropImage($targetWidth, $targetHeight, $offset['x'], $offset['y']);

        return $this->originalImage;
    }

    /**
     * Returns width and height for resizing the image, keeping the aspect ratio
     * and allow the image to be larger than either the width or height
     */
    protected function getSafeResizeOffset(Imagick $image, int $targetWidth, int $targetHeight): array
    {
        $source = $image->getImageGeometry();
        if (0 == $targetHeight || ($source['width'] / $source['height']) < ($targetWidth / $targetHeight)) {
            $scale = $source['width'] / $targetWidth;
        } else {
            $scale = $source['height'] / $targetHeight;
        }

        return [
            'width' => (int) ($source['width'] / $scale), 
            'height' => (int) ($source['height'] / $scale)
        ];
    }

    /**
     * Returns a YUV weighted greyscale value
     */
    protected function rgb2bw(int $r, int $g, int $b): float
    {
        return ($r * 0.299) + ($g * 0.587) + ($b * 0.114);
    }

    /**
     * Calculate entropy from histogram
     */
    protected function getEntropy(array $histogram, int $area): float
    {
        $value = 0.0;
        $colors = count($histogram);
        
        for ($idx = 0; $idx < $colors; $idx++) {
            // calculates the percentage of pixels having this color value
            $p = $histogram[$idx]->getColorCount() / $area;
            // A common way of representing entropy in scalar
            $value = $value + $p * log($p, 2);
        }
        
        // $value is always 0.0 or negative, so transform into positive scalar value
        return -$value;
    }

    /**
     * Calculate entropy from simple array histogram
     */
    protected function getEntropyFromArray(array $histogram, int $area): float
    {
        $value = 0.0;
        
        foreach ($histogram as $count) {
            // calculates the percentage of pixels having this color value
            $p = $count / $area;
            // A common way of representing entropy in scalar
            $value = $value + $p * log($p, 2);
        }
        
        // $value is always 0.0 or negative, so transform into positive scalar value
        return -$value;
    }

    /**
     * Set base dimensions
     */
    protected function setBaseDimensions(int $width, int $height): self
    {
        $this->baseDimension = ['width' => $width, 'height' => $height];
        return $this;
    }

    /**
     * Get base dimension
     */
    protected function getBaseDimension(string $key): int
    {
        if (isset($this->baseDimension)) {
            return $this->baseDimension[$key];
        }
        
        return match ($key) {
            'width' => $this->originalImage?->getImageWidth() ?? 0,
            'height' => $this->originalImage?->getImageHeight() ?? 0,
            default => 0
        };
    }

    /**
     * Applies EXIF orientation metadata to pixel data and removes the EXIF rotation
     */
    protected function autoOrient(): void
    {
        if (!$this->originalImage) {
            return;
        }
        
        // apply EXIF orientation to pixel data
        match ($this->originalImage->getImageOrientation()) {
            Imagick::ORIENTATION_BOTTOMRIGHT => $this->originalImage->rotateimage('#000', 180),
            Imagick::ORIENTATION_RIGHTTOP => $this->originalImage->rotateimage('#000', 90),
            Imagick::ORIENTATION_LEFTBOTTOM => $this->originalImage->rotateimage('#000', -90),
            default => null
        };

        // reset EXIF orientation
        $this->originalImage->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
    }

    /**
     * Get special offset for class
     */
    abstract protected function getSpecialOffset(Imagick $original, int $targetWidth, int $targetHeight): array;
}