<?php
namespace forevermatt\mosaic;

class SourceImage extends ComparableImage
{
    protected $alreadyUsedInMosaic = false;
    protected $desiredAspectRatio = null;
    protected $maxWidth = null;
    
    /**
     * Constructor.
     * 
     * @param string $pathToImage The path to the image file.
     * @param float $desiredAspectRatio (Optional:) If specified, the image at
     *     the specified path will be cropped to match the target aspect ratio.
     * @param int $maxWidth (Optional:) The max width to store of a copy of this
     *     image at (for internal use).
     */
    public function __construct(
        $pathToImage = null,
        $desiredAspectRatio = null,
        $maxWidth = null
    ) {
        $this->desiredAspectRatio = $desiredAspectRatio;
        $this->maxWidth = $maxWidth;
        parent::__construct($pathToImage);
    }
    
    /**
     * Calculate the path to a metadata file for this image.
     * 
     * @return string The metadata file path.
     */
    protected function calculateMetadataFilePath()
    {
        return sprintf(
            'temp/%s/%s.json',
            $this->desiredAspectRatio ?: 'any',
            $this->getFileNameHash()
        );
    }

    /**
     * Calculate the path to a temp file for this image.
     * 
     * @return string The temp file path.
     */
    protected function calculateTempFilePath()
    {
        return sprintf(
            'temp/%s/%s.jpg',
            $this->desiredAspectRatio ?: 'any',
            $this->getFileNameHash()
        );
    }

    protected function freeImageResourceMemory()
    {
        if ($this->imageResource === null) {
            return;
        }
        
        $tempFilePath = $this->calculateTempFilePath();
        
        // If we have not yet saved a temp file copy of this image, do so.
        if (! file_exists($tempFilePath)) {
            $tempFolderPath = dirname($tempFilePath);
            if (! is_dir($tempFolderPath)) {
                echo 'Creating folder "' . $tempFolderPath . '"...' . PHP_EOL;
                mkdir($tempFolderPath, 0777, true);
            }
            $this->saveAsJpg(
                $tempFilePath,
                95,
                $this->imageResource
            );
        }
        
        $this->imageResource = null;
    }
    
    protected function getFileNameHash()
    {
        return md5($this->getfileName());
    }
    
    public function getImageResourceFromImageFile()
    {
        $tempFilePath = $this->calculateTempFilePath();
        
        if (file_exists($tempFilePath)) {
            $imageResource = \imagecreatefromjpeg($tempFilePath);

            if ($imageResource === false) {
                throw new \Exception(
                    'Failed to read in image from temp file "' . $this->pathToImage . '".',
                    1439051725
                );
            }
        } else {
            $imageResource = parent::getImageResourceFromImageFile();
        }
        
        return $imageResource;
    }
    
//    protected function getImageResourceFromJpgFile($pathToJpg)
//    {
//        $imageResource = null;
//        try {
//            $exifThumbnail = \exif_thumbnail($pathToJpg);
//            if ($exifThumbnail !== false) {
//                $imageResource = \imagecreatefromstring($exifThumbnail);
//                $thumbnailWidth = \imagesx($imageResource);
//                if ($this->maxWidth !== null) {
//                    if ($thumbnailWidth < $this->maxWidth) {
//                        $imageResource = null;
//                        echo sprintf(
//                            'Note: Thumbnail too small (%s < %s) for "%s".%s',
//                            $thumbnailWidth,
//                            $this->maxWidth,
//                            $pathToJpg,
//                            PHP_EOL
//                        );
//                    }
//                }
//            }
//        } catch (\Exception $e) {
//            echo sprintf(
//                'Note: Unable to read thumbnail from "%s".%s',
//                $pathToJpg,
//                PHP_EOL
//            );
//        }
//        
//        if (! $imageResource) {
//            $imageResource = parent::getImageResourceFromJpgFile($pathToJpg);
//        }
//        
//        return $imageResource;
//    }
    
    protected function getMetadata()
    {
        $metadataFilePath = $this->calculateMetadataFilePath();
        if ( ! file_exists($metadataFilePath)) {
            return null;
        }
        
        $metadataJson = file_get_contents($metadataFilePath);
        if ($metadataJson === false) {
            return null;
        }
        
        return json_decode($metadataJson);
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
    
    public function loadImage()
    {
        $this->rejectIfKnownToHaveWrongOrientation();
        
        parent::loadImage();
        
        $this->recordMetadataIfNotDoneYet();
        
        $imageResource = $this->imageResource;
        
        if ($this->desiredAspectRatio !== null) {
            
            $requiredOrientation = Image::getOrientationFromAspectRatio(
                $this->desiredAspectRatio
            );

            // Only accept proper orientation pictures.
            if ( ! $this->hasOrientation($requiredOrientation)) {
                throw new \Exception(
                    'Incorrect orientation (should be ' . $requiredOrientation . ').',
                    1441638938
                );
            }

            if ($this->desiredAspectRatio !== $this->getAspectRatio()) {
                $imageResource = self::cropImageResourceToAspectRatio(
                    $imageResource,
                    $this->desiredAspectRatio
                );
            }
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
        
        $this->setImageResource($imageResource);
        
        $this->generateSignature();
        
        $this->freeImageResourceMemory();
    }
    
    public function markAsUsed()
    {
        $this->alreadyUsedInMosaic = true;
    }

    /**
     * Record metadata to a file (if the metadata file has not yet been
     * created).
     * 
     * NOTE: This should be done AFTER loading in the imageResource from the
     * original image file but BEFORE any cropping is done to it. This also
     * means that data such as the aspect ratio is that of the original file,
     * not necessarily the version used in the mosaic (which may have been
     * cropped).
     */
    protected function recordMetadataIfNotDoneYet()
    {
        $metadataFilePath = $this->calculateMetadataFilePath();
        
        // If we have not yet saved a metadata file for this image, do so.
        if ( ! file_exists($metadataFilePath)) {
            
            $metadataFolderPath = dirname($metadataFilePath);
            if ( ! is_dir($metadataFolderPath)) {
                echo 'Creating folder "' . $metadataFolderPath . '"...' . PHP_EOL;
                mkdir($metadataFolderPath, 0777, true);
            }
            
            file_put_contents(
                $metadataFilePath,
                json_encode(array(
                    'ar' => $this->getAspectRatio(),
                ))
            );
        }
    }
    
    protected function rejectIfKnownToHaveWrongOrientation()
    {
        if ($this->desiredAspectRatio === null) {
            throw new \Exception(
                'We can only reject source images know to have the wrong '
                . 'orientation if given a desired aspect ratio.',
                1447550416
            );
        }
        
        $metadata = $this->getMetadata();
        
        if (($metadata !== null) && (array_key_exists('ar', $metadata))) {
            
            $actualAspectRatio = $metadata->ar;
            $actualOrientation = Image::getOrientationFromAspectRatio(
                $actualAspectRatio
            );
            $requiredOrientation = Image::getOrientationFromAspectRatio(
                $this->desiredAspectRatio
            );

            if ($actualOrientation !== $requiredOrientation) {
                throw new \Exception(
                    'Known to have incorrect orientation (should be '
                    . $requiredOrientation . ').',
                    1447550306
                );
            }
        }
    }
}
