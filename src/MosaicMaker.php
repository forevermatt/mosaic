<?php
namespace forevermatt\mosaic;

class MosaicMaker
{
    public static function calculateSimilarity($pathToImage1, $pathToImage2)
    {
        if ( ! Image::isImageFile($pathToImage1)) {
            throw new \InvalidArgumentException(
                sprintf('Error: "%s" is not an image file.', $pathToImage1),
                1449430004
            );
        }
        
        if ( ! Image::isImageFile($pathToImage2)) {
            throw new \InvalidArgumentException(
                sprintf('Error: "%s" is not an image file.', $pathToImage2),
                1449430075
            );
        }
        
        $image1 = new ComparableImage($pathToImage1);
        $image2 = new Image($pathToImage2);
        
        return $image1->calculateSimilarityTo($image2);
    }
    
    protected static function listImageFilesInFolders($pathsToFolders)
    {
        $files = array();
        foreach ($pathsToFolders as $pathToFolder) {
            $folderIterator = new \RecursiveDirectoryIterator($pathToFolder);

            foreach (new \RecursiveIteratorIterator($folderIterator) as $filePath)
            {
                // Filter out "." and "..".
                if ($filePath->isDir()) {
                    continue;
                }

                if (Image::isImageFile($filePath)) {
                    $files[] = $filePath;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Make a mosaic that looks like the specified guide image using the
     * source images found in the specified folder.
     * 
     * @param string $pathToGuideImage The path to the guide image.
     * @param array $pathsToSourceImagesFolders An array of the path(s) to the
     *     folder(s) that contain the sources images.
     * @return string The path to the newly created mosaic image.
     */
    public static function makeMosaic(
        $pathToGuideImage,
        $pathsToSourceImagesFolders,
        $verboseOutput = false
    ) {
        $guideImage = new GuideImage($pathToGuideImage);
        
        $sourceImageFiles = self::listImageFilesInFolders(
            $pathsToSourceImagesFolders
        );
        
        $sourceImages = array();
        $numSourceImageFiles = count($sourceImageFiles);
        $numProcessedSourceImages = 0;
        $progressMeter = new ProgressMeter();
        foreach ($sourceImageFiles as $sourceImageFile) {
            try {
                $sourceImages[] = new SourceImage(
                    $sourceImageFile,
                    4/3,
                    120
                );
            } catch (\Exception $e) {
                if ($verboseOutput) {
                    echo sprintf(
                        'Skipping "%s": %s%s',
                        $sourceImageFile,
                        $e->getMessage(),
                        PHP_EOL
                    );
                }
            }
            $numProcessedSourceImages += 1;
            $progressMeter->showProgress(
                'Loading source images (1/4)',
                $numProcessedSourceImages / $numSourceImageFiles
            );
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
