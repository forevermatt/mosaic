<?php

namespace forevermatt\mosaic;

class ImageSlice extends ComparableImage
{
    public $xOffsetInParent = 0;
    public $yOffsetInParent = 0;
    
    public function getFileName()
    {
        return sprintf(
            'guide(%s, %s)',
            $this->xOffsetInParent,
            $this->yOffsetInParent
        );
    }
}
