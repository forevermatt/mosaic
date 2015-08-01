<?php

namespace forevermatt\mosaic;

class GuideImage extends Image
{
    /**
     * Create a new GuideImage.
     * 
     * @param string $pathToImage The path to the guide image file.
     */
    public function __construct($pathToImage = null)
    {
        // Make sure the guide image is cached in memory.
        parent::__construct($pathToImage, null, null, true);
    }
}
