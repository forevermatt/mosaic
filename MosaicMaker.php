<?php

namespace forevermatt\mosaic;

class MosaicMaker
{
    /**
     * Make a mosaic that looks like the specified guide image using the
     * specified source images.
     * 
     * @param string $pathToGuideImage The path to the guide image.
     * @param array $pathsToSourceImages An array of (string) paths to the
     *     source images.
     * @return string The path to the mosaic image.
     */
    public static function makeMosaic($pathToGuideImage, $pathsToSourceImages)
    {
        //  Get the guide image.
        $guideImage = self::getImage($pathToGuideImage);
        
        // Get the source images.
        $sourceImages = self::getImages($pathsToSourceImages);
        
        // Slice up the guide image into no more than the number of source
        // images.
        $guideImageSlices = self::sliceImage($guideImage, count($sourceImages));
        
        
    }
    
    /**
     * Read in the image at the specified path and return it.
     * 
     * @param string $pathToImage The path to the image.
     * @return resource An image resource identifier.
     */
    public static function getImage($pathToImage)
    {
        $imageResource = imagecreatefromjpeg($pathToImage);
        
        if ($imageResource === false) {
            throw new Exception(
                'Failed to read in image from "' . $pathToImage . '".',
                1424348816
            );
        }
        
        return $imageResource;
    }
    
    /**
     * Read in the images at the specified paths and return them.
     * 
     * @param array $pathsToImages An array of (string) paths to the images.
     * @return array An array of image resource identifiers.
     */
    public static function getImages($pathsToImages)
    {
        $imageResources = array();
        
        foreach ($pathsToImages as $pathToImage) {
            $imageResources[] = self::getImage($pathToImage);
        }
        
        return $imageResources;
    }
    
    /**
     * Slice the given image into no more than the specified number of slices.
     * The slices will have the same aspect ratio as the image.
     * 
     * @param resource $image The image to slice.
     * @param int $maxNumSlices The (inclusive) maximum number of slices.
     * @return array An array of image resource identifiers (each holding one of
     *     the slices).
     */
    public static function sliceImage($image, $maxNumSlices)
    {
        // Get the aspect ratio of the image.
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        
        // Get the largest factor of 4 that's no bigger than our max-slice
        // limit. (Basically, we'll slice the image in half both ways as many
        // times as we can).
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
        
        // Extract all of the slices from the image.
        $slices = array();
        for ($hSliceOffset = 0; $hSliceOffset < $numSlicesPerDirection; $hSliceOffset++) {
            for ($vSliceOffset = 0; $vSliceOffset < $numSlicesPerDirection; $vSliceOffset++) {
                
                // Figure out where this slice will start.
                $xStart = $hSliceOffset * $hPixelsPerSlice;
                $yStart = $vSliceOffset * $vPixelsPerSlice;
                
                // Extract the slice from the full image.
                $slices[] = self::getSliceFromImage(
                    $image,
                    $xStart,
                    ($xStart + $hPixelsPerSlice),
                    $yStart,
                    ($yStart + $vPixelsPerSlice)
                );
            }
        }
        
        // Return the resulting array of slices.
        return $slices;
    }
    
    /**
     * Extract a slice from the given image. Any necessary rounding to end up
     * with whole pixels will be done as late in the calculations as possible.
     * 
     * @param resource $image The image resource from which to extract a slice.
     * @param float $xStart The horizontal offset (from the left edge) where the
     *     slice should start, in pixels.
     * @param float $xStop The horizontal offset (from the left edge) where the
     *     slice should stop, in pixels.
     * @param float $yStart The vertical offset (below the top edge) where the
     *     slice should start, in pixels.
     * @param float $yStop The vertical offset (below the top edge) where the
     *     slice should stop, in pixels.
     */
    public static function getSliceFromImage(
        $image,
        $xStart,
        $xStop,
        $yStart,
        $yStop
    ) {
        // Calculate the target dimensions.
        $width = round($xStop - $xStart);
        $height = round($yStop - $yStart);
        
        // Create the image resource into which the slice will be put.
        $slice = imagecreatetruecolor($width, $height);
        $success = imagecopy(
            $slice,
            $image,
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
        
        // Otherwise return the extracted image slice.
        return $slice;
    }
}
