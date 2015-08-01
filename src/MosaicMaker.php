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
        // Step A: Get the guide image.
        $guideImage = new GuideImage($pathToGuideImage);
        
        $guideImageAspectRatio = $guideImage->getAspectRatio();
        
        // Step B: Get the source images.
        $sourceImages = array();
        foreach ($pathsToSourceImages as $pathToSourceImage) {
            if (Image::isImageFile($pathToSourceImage)) {
                try {
                    $sourceImages[] = new Image(
                        $pathToSourceImage,
                        $guideImageAspectRatio,
                        100
                    );
                } catch (\Exception $e) {
                    echo 'Skipping "' . $pathToSourceImage . '".' . PHP_EOL;
                }
            }
        }
        
        // Create a mosaic from those slices/images.
        $mosaic = new Mosaic($guideImage, $sourceImages);
        
        // Generate a filename for the new mosaic image.
        $mosaicFilename = 'Mosaic_' . microtime(true) . '.jpg';
        $filePathToMosaic = dirname($pathToGuideImage) . '/' . $mosaicFilename;
        
        // Steps D & E: (Lazily generate and) save the mosaic image.
        $mosaic->saveAs($filePathToMosaic);
        return $filePathToMosaic;
    }
}
