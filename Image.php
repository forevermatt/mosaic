<?php

namespace forevermatt\mosaic;

class Image
{
    const PRECISION_LOW = 1;
    const PRECISION_MEDIUM = 2;
    const PRECISION_HIGH = 3;
    
    public $lowPrecisionSignature = null;
    public $mediumPrecisionSignature = null;
    public $highPrecisionSignature = null;
    
    public $imageResource = null;
    
    public function __construct($imageResource = null)
    {
        $this->imageResource = $imageResource;
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
}
