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
        $guideImage = new Image($pathToGuideImage);
        
        // Get the source images.
        $sourceImages = array();
        foreach ($pathsToSourceImages as $pathToSourceImage) {
            $sourceImages[] = new Image($pathToSourceImage);
        }
        
        // Slice up the guide image into no more than the number of source
        // images.
        $guideImageSlices = $guideImage->slice(count($sourceImages));
        
        // Create a mosaic from those slices/images.
        $mosaic = new Mosaic($guideImageSlices, $sourceImages);
        
        // Generate a filename for the new mosaic image.
        $mosaicFilename = 'Mosaic_' . time() . '.jpg';
        
        // Save the mosaic image.
        $mosaic->saveAs(dirname($pathToGuideImage) . '/' . $mosaicFilename);
    }
}
