<?php
namespace forevermatt\mosaic;

class Image
{
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
        if (! $this->isImageResourceLoaded()) {
            if ($this->hasPathToImage()) {
                $this->loadImage();
            }
        }
        return $this->imageResource;
    }
    
    public function getImageResourceFromImageFile()
    {
        $fileExtension = self::getFileExtension($this->pathToImage);
        if (($fileExtension === 'jpg') || ($fileExtension === 'jpeg')) {
            $imageResource = @imagecreatefromjpeg($this->pathToImage);
        } elseif ($fileExtension === 'png') {
            $imageResource = imagecreatefrompng($this->pathToImage);
        } else {
            throw new \Exception(
                'Unknown file format: ' . $fileExtension,
                1437785032
            );
        }
        
        if ($imageResource === false) {
            throw new \Exception(
                'Failed to read in image from "' . $this->pathToImage . '".',
                1424348816
            );
        }
        
        return $imageResource;
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
    
    protected function isImageResourceLoaded()
    {
        return ($this->imageResource !== null);
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
