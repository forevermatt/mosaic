<?php

namespace forevermatt\mosaic;

class Image
{
    protected $signatures = array();
    
    protected $pathToImage = null;
    protected $imageResource = null;
    
    protected $width = null;
    protected $height = null;
    
    protected $desiredAspectRatio = null;
    
    protected $maxWidth = null;
//    protected $maxHeight = null;
    
    protected $alreadyUsedInMosaic = false;
    
    /**
     * Create a new Image object, optionally specifying the path to a file to
     * read in (when necessary).
     * 
     * @param string $pathToImage The path to the image file.
     * @param float $desiredAspectRatio (Optional:) If specified, the image at
     *     the specified path will be cropped to match the target aspect ratio.
     * @param int $maxWidth (Optional:) The max width to store of a copy of this
     *     image at (for internal use).
     * 
     * @param int $maxHeight (Optional:) The max height to store of a copy of
     *     this image at (for internal use).
     */
    public function __construct(
        $pathToImage = null,
        $desiredAspectRatio = null,
        $maxWidth = null,
        $cacheInMemory = false
    ) {
        $this->pathToImage = $pathToImage;
        $this->desiredAspectRatio = $desiredAspectRatio;
        $this->maxWidth = $maxWidth;
        
        if ($pathToImage) {
            $this->imageResource = $this->loadImage($this->pathToImage);
            $this->getWidth();
            $this->getHeight();
            $this->getSignature(3);

            if ($cacheInMemory) {
                echo 'Cache "' . $this->getFileName() . '".' . PHP_EOL;
            } else {
                $this->imageResource = null;
            }
        }
    }
    
    /**
     * Get the actual color difference between the two given signature images.
     * 
     * @param Image $signature1 The first image's signature.
     * @param Image $signature2 The second image's signature.
     * @return int The difference between the two signatures Images.
     */
    protected function getAbsoluteDifference($otherSignature)
    {
        // Get the dimensions of the two signature images.
        $width1 = $this->getWidth();
        $height1 = $this->getHeight();
        $width2 = $otherSignature->getWidth();
        $height2 = $otherSignature->getHeight();

        // If the two signature images are different, stop.
        if (($width1 !== $width2) || ($height1 !== $height2)) {
            throw new \Exception(
                'Cannot compare signature images of different precision '
                . 'levels.',
                1425386364
            );
        }

        $imageResource1 = $this->getImageResource();
        $imageResource2 = $otherSignature->getImageResource();

        $totalDifference = 0;

        // For each pixel in the images, add up the color differences.
        for ($x = 0; $x < $width1; $x++) {
            for ($y = 0; $y < $height1; $y++) {
                $color1 = imagecolorsforindex(
                    $imageResource1,
                    imagecolorat($imageResource1, $x, $y)
                );
                $color2 = imagecolorsforindex(
                    $imageResource2,
                    imagecolorat($imageResource2, $x, $y)
                );
                $pixelDifference = abs($color1['red'] - $color2['red'])
                    + abs($color1['green'] - $color2['green'])
                    + abs($color1['blue'] - $color2['blue']);
                $totalDifference += $pixelDifference;
            }
        }

        return $pixelDifference;
    }
    
    /**
     * Compare this image with the given image using signatures (i.e. downsized
     * copies of the each image) at the specified precision level (>= 1, higher
     * equals more precise).
     * 
     * @param Image $otherImage The Image to compare this Image with.
     * @param int $precision How precise a comparison to do.
     * @return int The difference between the two signatures at that precision
     *     level.
     */
    public function compareWith($otherImage, $precision = 1)
    {
        // Get this image's signature.
        $thisSignature = $this->getSignature($precision);
        
        // Get the other image's signature.
        $otherSignature = $otherImage->getSignature($precision);
        
        // Return the difference of the two signature images.
        return $thisSignature->getAbsoluteDifference($otherSignature);
    }
    
    public static function cropImageResourceToAspectRatio(
        $imageResource,
        $targetAspectRatio
    ) {
        echo 'Cropping image...' . PHP_EOL;
        
        $imageWidth = \imagesx($imageResource);
        $imageHeight = \imagesy($imageResource);
        $imageAspectRatio = $imageWidth / $imageHeight;
        
        // If the given image resource is too wide...
        if ($imageAspectRatio > $targetAspectRatio) {
            
            // Use the full height, but a narrower width.
            $heightToUse = $imageHeight;
            $verticalOffset = 0;
            $widthToUse = (int)round($imageHeight * $targetAspectRatio);
            $horizontalOffset = (int)round(($imageWidth - $widthToUse) / 2);
            
        } else {
            
            // Use the full width, but a shorter height.
            $heightToUse = (int)round($imageWidth / $targetAspectRatio);;
            $verticalOffset = (int)round(($imageHeight - $heightToUse) / 2);
            $widthToUse = $imageWidth;
            $horizontalOffset = 0;
        }
        
        $croppedImageResource = imagecreatetruecolor($widthToUse, $heightToUse);
        $successful = imagecopy(
            $croppedImageResource,
            $imageResource,
            0,
            0,
            $horizontalOffset,
            $verticalOffset,
            $widthToUse,
            $heightToUse
        );
        
        if ( ! $successful) {
            throw new \Exception(
                sprintf(
                    'Failed to crop image down to %sx%s.%s',
                    $widthToUse,
                    $heightToUse,
                    PHP_EOL
                ),
                1435259752
            );
        }
        
        return $croppedImageResource;
        
        //// TEMP
        //die(var_dump(
        //    $imageWidth,
        //    $imageHeight,
        //    $imageAspectRatio,
        //    $targetAspectRatio,
        //    $widthToUse,
        //    $heightToUse,
        //    $horizontalOffset,
        //    $verticalOffset
        //));
    }
    
    public function getAspectRatio()
    {
        return ($this->getWidth() / $this->getHeight());
    }
    
    /**
     * Retrieve the image resource represented by this Image.
     * 
     * @return resource
     */
    public function getImageResource()
    {
        if ($this->imageResource === null) {
            if ($this->pathToImage !== null) {
                return $this->loadImage($this->pathToImage);
            }
        }
        return $this->imageResource;
    }
    
    public function getFileExtension($pathToImage)
    {
        $finalDotIndex = strrpos($pathToImage, '.');
        return strtolower(substr($pathToImage, $finalDotIndex + 1));
    }
    
    public function getFileName()
    {
        return is_null($this->pathToImage) ? null : basename($this->pathToImage);
    }
    
    public function getHeight()
    {
        if ($this->height === null) {
            $this->height = \imagesy($this->getImageResource());
            if ($this->height === false) {
                throw new \Exception(
                    "Failed to retrieve the image's height.",
                    1424867982
                );
            }
        }
        return $this->height;
    }
    
    /**
     * Get a "signature" of this Image by downsizing it to a very few pixels.
     * 
     * @param int $precision The number of pixels-per-side to downsize the image
     *     to for comparing with another image's signature.
     * @return Image The signature image (to use for comparing).
     */
    public function getSignature($precision)
    {
        // If we haven't yet calculated the signature at the indicated
        // precision, do so.
        if ( ! array_key_exists($precision, $this->signatures)) {
            
            // Make sure we were given a valid precision value.
            if ($precision < 1) {
                throw new \Exception(
                    sprintf(
                        'Precision values must be positive integers (not %s).',
                        var_export($precision, true)
                    ),
                    1434765882
                );
            }
            
            // Resize the image resource down to the size necessary for the
            // specified precision level.
            $sizedImage = $this->getSizedImage(
                $precision,
                $precision
            );
            $this->signatures[$precision] = $sizedImage;
        }
        return $this->signatures[$precision];
    }
    
    public function getWidth()
    {
        if ($this->width === null) {
            $this->width = \imagesx($this->getImageResource());
            if ($this->width === false) {
                throw new \Exception(
                    "Failed to retrieve the image's width.",
                    1424868009
                );
            }
        }
        return $this->width;
    }
    
    /**
     * Read in the image data from the file at the specified path.
     * 
     * @param string $pathToImage The path to the image file.
     * @return resource The image resource.
     */
    public function loadImage($pathToImage)
    {
        echo sprintf(
            'Loading image "%s"...%s',
            $this->getFileName(),
            PHP_EOL
        );
        
        $fileExtension = self::getFileExtension($pathToImage);
        if (($fileExtension === 'jpg') || ($fileExtension === 'jpeg')) {
            $imageResource = imagecreatefromjpeg($pathToImage);
        } elseif ($fileExtension === 'png') {
            $imageResource = imagecreatefrompng($pathToImage);
        } else {
            throw new \Exception(
                'Unknown file format: ' . $fileExtension,
                1437785032
            );
        }
        
        if ($imageResource === false) {
            throw new \Exception(
                'Failed to read in image from "' . $pathToImage . '".',
                1424348816
            );
        }
        
        if ($this->desiredAspectRatio !== null) {
            
            $imageResource = self::cropImageResourceToAspectRatio(
                $imageResource,
                $this->desiredAspectRatio
            );
        }
        
        if ($this->maxWidth !== null) {
            $imageWidth = \imagesx($imageResource);
            if ($imageWidth > $this->maxWidth) {
                $downsizedWidth = $this->maxWidth;
                $downsizedHeight = $downsizedWidth / $this->desiredAspectRatio;
                
                $imageResource = self::resizeImageResource(
                    $imageResource,
                    $downsizedWidth,
                    $downsizedHeight
                );
            }
        }
        
//        if (($this->maxWidth !== null) && ($this->maxHeight !== null)) {
//            
//            $sizedIiamgeResource = imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
//        }
        
        return $imageResource;
        
//        // TEMP
//        $this->saveAsJpg(
//            $this->getWidth() . 'x' . $this->getHeight() . '_'
//            . $this->getFileName()
//        );
    }
    
    /**
     * Get a copy of this Image at the specified width and height.
     * 
     * @param int $desiredWidth The desired width (in whole pixels).
     * @param int $desiredHeight The desired height (in whole pixels).
     * @return Image The resized Image.
     * @throws \Exception
     */
    public function getSizedImage($desiredWidth, $desiredHeight)
    {
        echo sprintf(
            'Resizing %s to %sx%s...%s',
            var_export($this->getFileName(), true),
            $desiredWidth,
            $desiredHeight,
            PHP_EOL
        );
        
        $imageResource = $this->getImageResource();
        
        $resizedImageResource = self::resizeImageResource(
            $imageResource,
            $desiredWidth,
            $desiredHeight
        );
        
        // Otherwise return the resized image resource as a new Image.
        $sizedImage = new Image();
        $sizedImage->setImageResource($resizedImageResource);
        return $sizedImage;
    }
    
    /**
     * Get a copy of this Image at the specified width and height.
     * 
     * @param int $desiredWidth The desired width (in whole pixels).
     * @param int $desiredHeight The desired height (in whole pixels).
     * @return Image The resized Image.
     * @throws \Exception
     */
    public static function resizeImageResource(
        $imageResource,
        $desiredWidth,
        $desiredHeight
    ) {
        // Calculate the current dimensions.
        $initialWidth = \imagesx($imageResource);
        $initialHeight = \imagesy($imageResource);
        
        // Create the image resource into which the slice will be put.
        $resizedImageResource = imagecreatetruecolor(
            $desiredWidth,
            $desiredHeight
        );
        
        // Do the resize.
        $success = imagecopyresampled(
            $resizedImageResource,
            $imageResource,
            0,
            0,
            0,
            0,
            $desiredWidth,
            $desiredHeight,
            $initialWidth,
            $initialHeight
        );
        
        // Stop if something went wrong.
        if ( ! $success) {
            throw new \Exception(
                'Failed to resize the image.',
                1424830747
            );
        }
        
        return $resizedImageResource;
    }
    
    /**
     * Find out whether this Image has already been marked as "used".
     * 
     * @return bool
     */
    public function isAvailable()
    {
        return ( ! $this->alreadyUsedInMosaic);
    }
    
    public function markAsUsed()
    {
        $this->alreadyUsedInMosaic = true;
    }
    
    /**
     * Save the image as a JPG.
     * 
     * @param string $pathAndFilename The full path for the file to write to.
     * @param int $quality (Optional:) The JPG quality to use (0-100).
     * @return boolean Whether successful.
     */
    public function saveAsJpg($pathAndFilename, $quality = 95)
    {
        return imagejpeg(
            $this->getImageResource(),
            $pathAndFilename,
            $quality
        );
    }
    
    /**
     * Set the image resource represented by this Image.
     * 
     * @param resource $imageResource The new image resource.
     */
    public function setImageResource($imageResource)
    {
        // Save the given image resource.
        $this->imageResource = $imageResource;
        
        // Forget any path that may have been set to an image file, since that
        // is no longer where this image's data came from (as far as we know).
        $this->pathToImage = null;
    }
    
    /**
     * Slice this Image into no more than the specified number of slices. The
     * slices will (essentially) have the same aspect ratio as the image (within
     * a pixel each direction).
     * 
     * @param int $maxNumSlices The (inclusive) maximum number of slices.
     * @return array An array of Images (each holding one of the slices).
     */
    public function slice($maxNumSlices)
    {
        // Get the aspect ratio of the image.
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();
        
        // Get the largest factor of 4 that's no bigger than our max-slice
        // limit. (Basically, we'll slice the guide image in half both ways as
        // many times as we can).
        $numSlices = 4;
        while ($numSlices <= $maxNumSlices) {
            $numSlices *= 4;
        }
        $numSlices /= 4;
        $numSlicesPerDirection = sqrt($numSlices);
        
        // Figure out the number of pixels (horizontal and vertical) in each
        // slice.
        $hPixelsPerSlice = $imageWidth / $numSlicesPerDirection;
        $vPixelsPerSlice = $imageHeight / $numSlicesPerDirection;
        
        // Extract all of the slices of the image.
        $slices = array();
        for ($hSliceOffset = 0; $hSliceOffset < $numSlicesPerDirection; $hSliceOffset++) {
            for ($vSliceOffset = 0; $vSliceOffset < $numSlicesPerDirection; $vSliceOffset++) {
                
                // Figure out where this slice will start.
                $xStart = $hSliceOffset * $hPixelsPerSlice;
                $yStart = $vSliceOffset * $vPixelsPerSlice;
                
                // Extract the slice from the full image.
                $slices[] = $this->getSlice(
                    $xStart,
                    ($xStart + $hPixelsPerSlice),
                    $yStart,
                    ($yStart + $vPixelsPerSlice)
                );
            }
        }
        
        // Return the resulting array of Images sliced from the original.
        return $slices;
    }
    
    /**
     * Extract a slice from this image. Any necessary rounding to end up with
     * whole pixels will be done as late in the calculations as possible.
     * 
     * @param float $xStart The horizontal offset (from the left edge) where the
     *     slice should start, in pixels.
     * @param float $xStop The horizontal offset (from the left edge) where the
     *     slice should stop, in pixels.
     * @param float $yStart The vertical offset (below the top edge) where the
     *     slice should start, in pixels.
     * @param float $yStop The vertical offset (below the top edge) where the
     *     slice should stop, in pixels.
     * @return ImageSlice
     * @throws \Exception
     */
    public function getSlice(
        $xStart,
        $xStop,
        $yStart,
        $yStop
    ) {
        // Calculate the target dimensions.
        $width = round($xStop - $xStart);
        $height = round($yStop - $yStart);
        
        // Calculate the whole-number width and height.
        $xStartRounded = round($xStart);
        $yStartRounded = round($yStart);
        
        // Create the image resource into which the slice will be put.
        $sliceImageResource = imagecreatetruecolor($width, $height);
        $success = imagecopy(
            $sliceImageResource,
            $this->getImageResource(),
            0,
            0,
            $xStartRounded,
            $yStartRounded,
            $width,
            $height
        );
        
        // Stop if something went wrong.
        if ( ! $success) {
            throw new \Exception(
                'Failed to extract a slice from the image.',
                1424780302
            );
        }
        
        // Create an Image object from that image resource.
        $slice = new ImageSlice();
        $slice->setImageResource($sliceImageResource);
        $slice->xOffsetInParent = $xStartRounded;
        $slice->yOffsetInParent = $yStartRounded;
        
        // Otherwise return the extracted image slice.
        return $slice;
    }
}
