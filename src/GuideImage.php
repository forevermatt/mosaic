<?php

namespace forevermatt\mosaic;

class GuideImage extends Image
{
    public function __construct(
        $pathToImage = null,
        $desiredAspectRatio = null,
        $maxWidth = null,
        $cacheInMemory = false
    ) {
        parent::__construct(
            $pathToImage,
            $desiredAspectRatio,
            $maxWidth,
            $cacheInMemory
        );
    }
}
