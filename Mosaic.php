<?php

namespace forevermatt\mosaic;

class Mosaic
{
    /**
     * Create a new Mosaic from the given sets of images.
     * 
     */
    public function __construct($guideImageSlices, $sourceImages)
    {
        $this->guideImageSlices = $guideImageSlices;
        $this->sourceImages = $sourceImages;
    }
    
    
    
}
