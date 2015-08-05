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
        parent::__construct($pathToImage);
        $this->imageResource = $this->loadImage($this->pathToImage);
        $this->getWidth();
        $this->getHeight();
    }
}
