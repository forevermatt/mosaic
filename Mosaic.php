<?php

namespace forevermatt\mosaic;

class Mosaic
{
    protected $image = null;
    
    /**
     * Create a new Mosaic from the given sets of images.
     * 
     */
    public function __construct($guideImageSlices, $sourceImages)
    {
        $this->guideImageSlices = $guideImageSlices;
        $this->sourceImages = $sourceImages;
    }
    
    public function generateImage()
    {
        $imageResource = ...
        
        
        
        
        
        
        
        $mosaicImage = new Image();
        $mosaicImage->setImageResource($imageResource);
        return $mosaicImage;
    }
    
    public function getAsImage()
    {
        if ($this->image === null) {
            $this->image = $this->generateImage();
        }
        return $this->image;
    }
    
    public function saveAs($pathAndFilename)
    {
        $image = $this->getAsImage();
        $image->saveAsJpg($pathAndFilename);
    }
}
