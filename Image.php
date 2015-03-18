<?php

namespace forevermatt\mosaic;

class Image
{
    const MAX_SIGNATURE_PRECISION = 3;
    
    protected $signatures = array();
    
    protected $pathToImage = null;
    protected $imageResource = null;
    
    protected $width = null;
    protected $height = null;
    
    /**
     * Create a new Image object, optionally specifying the path to a file to
     * read in (when necessary).
     * 
     * @param string $pathToImage The path to the image file.
     */
    public function __construct($pathToImage = null)
    {
        $this->pathToImage = $pathToImage;
    }
    
    /**
     * Get the actual color difference between the two given signature images.
     * 
     * @param Image $signature1 The first image's signature.
     * @param Image $signature2 The second image's signature.
     * @return int The difference between the two signatures Images.
     */
    protected function getAbsoluteDifference($signature1, $signature2)
    {
        // Get the dimensions of the two signature images.
        $width1 = $signature1->getWidth();
        $height1 = $signature1->getHeight();
        $width2 = $signature2->getWidth();
        $height2 = $signature2->getHeight();
        
        // If the two signature images are different, stop.
        if (($width1 !== $width2) || ($height1 !== $height2)) {
            throw new \Exception(
                'Cannot compare signature images of different precision '
                . 'levels.',
                1425386364
            );
        }
        
        // For each pixel in the images, add up the color differences.
        ....
    }
    
    /**
     * Compare this image with the given image using signatures at the specified
     * precision level (>= 1, higher equals more precise).
     * 
     * @param Image $otherImage The Image to compare this Image with.
     * @param int $precision How precise a comparison to do.
     * @return int The difference between the two signatures at that precision
     *     level.
     */
    public function getSignatureDifference($otherImage, $precision)
    {
        // Get this image's signature.
        $thisSignature = $this->getSignature($precision);
        
        // Get the other image's signature.
        $otherSignature = $otherImage->getSignature($precision);
        
        // Return the difference of the two signature images.
        return $thisSignature->getAbsoluteDifference($otherSignature);
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
                $this->loadImage($this->pathToImage);
            }
        }
        return $this->imageResource;
    }
    
    public function getHeight()
    {
        if ($this->height === null) {
            $this->height = imagesy($this->getImageResource());
            if ($this->height === false) {
                throw new \Exception(
                    "Failed to retrieve the image's height.",
                    1424867982
                );
            }
        }
        return $this->width;
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
        if ( ! array_key_exists($this->signatures, $precision)) {
            
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
            $this->width = imagesx($this->getImageResource());
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
     * @param string $pathToImage The path to the image.
     */
    public function loadImage($pathToImage)
    {
        $imageResource = imagecreatefromjpeg($pathToImage);
        
        if ($imageResource === false) {
            throw new Exception(
                'Failed to read in image from "' . $pathToImage . '".',
                1424348816
            );
        }
        
        $this->imageResource = $imageResource;
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
        $imageResource = $this->getImageResource();
        
        // Calculate the current dimensions.
        $initialWidth = imagesx($imageResource);
        $initialHeight = imagesy($imageResource);
        
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
        
        // Otherwise return the resized image resource as a new Image.
        $sizedImage = new Image();
        $sizedImage->setImageResource($resizedImageResource);
        return $sizedImage;
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
        
        // Create the image resource into which the slice will be put.
        $sliceImageResource = imagecreatetruecolor($width, $height);
        $success = imagecopy(
            $sliceImageResource,
            $this->getImageResource(),
            0,
            0,
            round($xStart),
            round($yStart),
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
        $slice = new Image();
        $slice->setImageResource($sliceImageResource);
        
        // Otherwise return the extracted image slice.
        return $slice;
    }
}
