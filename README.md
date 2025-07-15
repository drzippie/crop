# Crop

This is a maintained fork of the original [stojg/crop](https://github.com/stojg/crop) library, which was archived on April 30, 2021. This fork continues development and adds modern improvements.

**Key enhancements in this fork:**
- ✅ Pure PHP face detection (no external extensions required)
- ✅ Modernized dependencies and compatibility
- ✅ Active maintenance and bug fixes

This is a small set of image croppers for automated cropping with intelligent algorithms.

## Requirements

 - PHP 8.3 or higher
 - ImageMagick extension with sRGB colorspace (version 6.7.5-5 or higher)
 - GD extension (for face detection)

## Description

This little project includes three functional image cropers:

### CropCenter

 This is the most basic of cropping techniques:

   1. Find the exact center of the image
   2. Trim any edges that is bigger than the targetWidth and targetHeight

### CropEntropy

This class finds the a position in the picture with the most "energy" in it. Energy (or entropy) in
images are defined by 'edginess' in the image. For example a image of the sky have low edginess and
an image of an anthill has very high edginess.

Energy is in this case calculated like this

  1. Take the image and turn it into black and white
  2. Run a edge filter so that we're left with only edges.
  3. Find a piece in the picture that has the highest entropy (i.e. most edges)
  4. Return coordinates that makes sure that this piece of the picture is not cropped 'away'

### CropBalanced

Crop balanced is a variant of CropEntropy where I tried to the cropping a bit more balanced.

  1. Dividing the image into four equally squares
  2. Find the most energetic point per square
  3. Finding the images weighted mean interest point for all squares

### CropFace

Crop face uses a pure PHP implementation of the Haar cascade algorithm for face detection.

In details, the FaceCrop uses Entropy Crop but puts blocking "limits" on the faces.
If the program faces two limits, we let the entropy decide the best crop.


## Usage
``` php
$center = new \drzippie\crop\CropCenter($filepath);
$croppedImage = $center->resizeAndCrop($width, $height);
$croppedImage->writeimage('assets/thumbs/cropped-center.jpg');
```
