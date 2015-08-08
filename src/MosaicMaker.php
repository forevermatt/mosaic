<?php
namespace forevermatt\mosaic;

class MosaicMaker
{
    protected static function listImageFilesInFolder($pathToFolder)
    {
        $files = array();
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
        
        return $files;
    }
    
    /**
     * Make a mosaic that looks like the specified guide image using the
     * source images found in the specified folder.
     * 
     * @param string $pathToGuideImage The path to the guide image.
     * @param string $pathToSourceImagesFolder The path to the folder that
     *     contains the sources images.
     * @return string The path to the newly created mosaic image.
     */
    public static function makeMosaic(
        $pathToGuideImage,
        $pathToSourceImagesFolder
    ) {
        $guideImage = new GuideImage($pathToGuideImage);
        
        $sourceImageFiles = self::listImageFilesInFolder(
            $pathToSourceImagesFolder
        );
        
        $guideImageAspectRatio = $guideImage->getAspectRatio();
        
        $sourceImages = array();
        foreach ($sourceImageFiles as $sourceImageFile) {
            try {
                $sourceImages[] = new SourceImage(
                    $sourceImageFile,
                    $guideImageAspectRatio,
                    640
                );
            } catch (\Exception $e) {
                echo 'Skipping "' . $sourceImageFile . '".' . PHP_EOL;
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
    
    protected function getSourceImagePaths($pathToSourceImagesFolder)
    {
        $pathToGuideImage = $argv[1];
        $pathsToSourceImages = array_merge(
            glob(realpath($argv[2]) . '/*.*'),       // = the specified folder.
            glob(realpath($argv[2]) . '/**/*.*'),    // = any immediate subfolders.
            glob(realpath($argv[2]) . '/**/**/*.*'), // = any 2nd-level subfolders.
            glob(realpath($argv[2]) . '/**/**/*.*')  // = any 3rd-level subfolders.
        );
    }
}
