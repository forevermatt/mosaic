<?php

namespace forevermatt\mosaic;

class Image
{
    const PRECISION_LOW = 1;
    const PRECISION_MEDIUM = 2;
    const PRECISION_HIGH = 3;
    
    protected $lowPrecisionSignature = null;
    protected $mediumPrecisionSignature = null;
    protected $highPrecisionSignature = null;
    
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
    
    public static function getSignature(
        $imageResource,
        $precision = self::PRECISION_LOW
    ) {
        // Resize the image resource down to the size necessary for the
        // specified precision level.
        $downsizedImage = self::resizeImage(
            $imageResource,
            $precision,
            $precision
        );
        
        // Calculate and return the signature.
        return new ImageSignature($downsizedImage);
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
     * Resize the given image resource to the given width and height.
     * 
     * @param resource $imageResource The image data to be resized.
     * @param int $newWidth The desired width (in whole pixels).
     * @param int $newHeight The desired height (in whole pixels).
     * @return resource The resized image resource.
     * @throws \Exception
     */
    public static function resizeImage($imageResource, $newWidth, $newHeight)
    {
        // Calculate the target dimensions.
        $initialWidth = imagesx($imageResource);
        $initialHeight = imagesy($imageResource);
        
        // Create the image resource into which the slice will be put.
        $resizedImageResource = imagecreatetruecolor($newWidth, $newHeight);
        $success = imagecopyresampled(
            $resizedImageResource,
            $imageResource,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
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
        
        // Otherwise return the resized image resource.
        return $resizedImageResource;
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
