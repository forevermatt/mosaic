<?php
namespace forevermatt\mosaic;

class Image
{
    const COLOR_MAX_VALUE = 255;
    const COLOR_VALUES_PER_PIXEL = 3;
    
    const ORIENTATION_LANDSCAPE = 'landscape';
    const ORIENTATION_PANORAMA_HORIZONTAL = 'panorama-horizontal';
    const ORIENTATION_PANORAMA_VERTICAL = 'panorama-vertical';
    const ORIENTATION_PORTRAIT = 'portrait';
    const ORIENTATION_SQUARE = 'square';
    
    protected $pathToImage = null;
    protected $imageResource = null;
    
    protected $width = null;
    protected $height = null;
    
    /**
     * Constructor.
     * 
     * @param string $pathToImage The path to the image file.
     */
    public function __construct($pathToImage = null)
    {
        $this->pathToImage = $pathToImage;
        if ($pathToImage) {
            $this->loadImage();
        }
    }
    
    public static function cropImageResourceToAspectRatio(
        $imageResource,
        $targetAspectRatio
    ) {
        //echo 'Cropping image...' . PHP_EOL;
        
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
    
    public static function getFileExtension($pathToImage)
    {
        $finalDotIndex = strrpos($pathToImage, '.');
        return strtolower(substr($pathToImage, $finalDotIndex + 1));
    }
    
    public function getFileName()
    {
        return $this->hasPathToImage() ? basename($this->pathToImage) : null;
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
     * Retrieve the image resource represented by this Image.
     * 
     * @return resource
     */
    public function getImageResource()
    {
        if ($this->hasImageResource()) {
            return $this->imageResource;
        } elseif ($this->hasPathToImage()) {
            return $this->getImageResourceFromImageFile();
        } else {
            throw new \Exception(
                'No image resource available, and no image file available to '
                . 'get the image resource from.',
                1439755183
            );
        }
    }
    
    protected function getImageResourceFromJpgFile($pathToImage)
    {
        $resourceFromFile = imagecreatefromjpeg($pathToImage);
        $potentiallyRotatedResource = $this->rotateIfNecessary(
            $resourceFromFile,
            $pathToImage
        );
        return $potentiallyRotatedResource;
    }
    
    protected function rotateIfNecessary($imageResource, $pathToImage)
    {
        $orientation = self::getOrientationFromExifData($pathToImage);
        if ($orientation === 6) {
            return imagerotate($imageResource, -90, 0);
        } elseif ($orientation === 8) {
            return imagerotate($imageResource, 90, 0);
        }
        return $imageResource;
    }
    
    public static function getOrientationFromExifData($pathToImage)
    {
        $exifData = exif_read_data($pathToImage);
        return $exifData['Orientation'] ?? null;
    }
    
    public function getImageResourceFromImageFile()
    {
        $fileExtension = self::getFileExtension($this->pathToImage);
        
        switch ($fileExtension) {
            
            case 'jpg':
            case 'jpeg':
                $imageResource = $this->getImageResourceFromJpgFile($this->pathToImage);
                break;

            case 'png':
                $imageResource = \imagecreatefrompng($this->pathToImage);
                break;

            default:
                throw new \Exception(
                    'Unknown file format: ' . $fileExtension,
                    1437785032
                );
                break;
        }
        
        if ($imageResource === false) {
            throw new \Exception(
                'Failed to read in image from "' . $this->pathToImage . '".',
                1424348816
            );
        }
        
        $this->setWidth(\imagesx($imageResource));
        $this->setHeight(\imagesy($imageResource));
        
        return $imageResource;
    }
    
    protected function getOrientation()
    {
        return self::getOrientationFromAspectRatio($this->getAspectRatio());
    }
    
    protected static function getOrientationFromAspectRatio($aspectRatio)
    {
        if ($aspectRatio >= 2) {
            return self::ORIENTATION_PANORAMA_HORIZONTAL;
        } elseif ($aspectRatio > 1) {
            return self::ORIENTATION_LANDSCAPE;
        } elseif ($aspectRatio <= 0.5) {
            return self::ORIENTATION_PANORAMA_VERTICAL;
        } elseif ($aspectRatio < 1) {
            return self::ORIENTATION_PORTRAIT;
        } else {
            return self::ORIENTATION_SQUARE;
        }
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
    
    public function hasOrientation($requiredOrientation)
    {
        return ($this->getOrientation() === $requiredOrientation);
    }
    
    protected function hasPathToImage()
    {
        return ($this->pathToImage !== null);
    }
    
    public static function isImageFile($pathToFile)
    {
        $fileExtension = self::getFileExtension($pathToFile);
        return in_array(
            $fileExtension,
            array('jpg', 'jpeg', 'png')
        );
    }
    
    protected function hasImageResource()
    {
        return ($this->imageResource !== null);
    }
    
    /**
     * Find out whether this image has the same dimensions as the given image.
     * 
     * @param Image $otherImage
     * @return bool
     */
    public function hasSameDimensionsAs($otherImage)
    {
        return (($this->getWidth() === $otherImage->getWidth()) &&
                ($this->getHeight() === $otherImage->getHeight()));
    }
    
    /**
     * Read in the image data from this Image's image file.
     */
    public function loadImage()
    {
        if (! $this->hasPathToImage()) {
            throw new \Exception(
                'Unable to load image (no path to image available).',
                1439047672
            );
        }
        
        //echo sprintf(
        //    'Loading image "%s"...%s',
        //    $this->getFileName(),
        //    PHP_EOL
        //);
        
        $imageResource = $this->getImageResourceFromImageFile();
        $this->setImageResource($imageResource);
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
        //echo sprintf(
        //    'Resizing %s to %sx%s...%s',
        //    var_export($this->getFileName(), true),
        //    $desiredWidth,
        //    $desiredHeight,
        //    PHP_EOL
        //);
        
        $imageResource = $this->getImageResource();
        
        $resizedImageResource = self::resizeImageResource(
            $imageResource,
            $desiredWidth,
            $desiredHeight
        );
        
        $sizedImage = new static();
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
        $this->imageResource = $imageResource;
        $this->setWidth(\imagesx($imageResource));
        $this->setHeight(\imagesy($imageResource));
    }
    
    protected function setHeight($height)
    {
        $this->height = $height;
    }
    
    protected function setWidth($width)
    {
        $this->width = $width;
    }
}
