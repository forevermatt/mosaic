<?php

namespace forevermatt\mosaic;

class ImageDiff
{
    /**
     * An array of key value pairs, where each key represents a precision
     * level, and each value represents the absolute difference (greater
     * values indicating greater difference) between the two images at that
     * precision level.
     *
     * @type array
     */
    protected $differences = array();

    protected $firstImage = null;
    protected $secondImage = null;
    
    /**
     * Create a new ImageDiff object from the given Images.
     * 
     * @param Image $firstImage The first image.
     * @param Image $secondImage The second image.
     */
    public function __construct($firstImage, $secondImage)
    {
        $this->firstImage = $firstImage;
        $this->secondImage= $secondImage;
    }
    
    
}
