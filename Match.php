<?php

namespace forevermatt\mosaic;

class Match
{
    protected $difference = null;
    
    /** @var ImageSlice */
    protected $slice = null;
    
    /** @var Image */
    protected $sourceImage = null;
    
    /**
     * Create a new Match object for handling data about how well a particular
     * guide image slice and a particular source image match each other.
     * 
     * @param Image $slice The guide image slice.
     * @param Image $sourceImage The source Image.
     * @param int $difference A measure of the difference between the two (where
     *     a lower value indicates less difference, and thus more similarity).
     */
    public function __construct(
        $slice = null,
        $sourceImage = null,
        $difference = null
    ) {
        $this->slice = $slice;
        $this->sourceImage = $sourceImage;
        $this->difference = $difference;
    }
    
    /**
     * Get the difference between the Match's images.
     * 
     * @return int
     */
    public function getDifference()
    {
        return $this->difference;
    }
    
    public function getSlice()
    {
        return $this->slice;
    }
    
    public function getSourceImage()
    {
        return $this->sourceImage;
    }
    
    /**
     * Find out whether this Match is better (aka. - has less difference) than
     * the given Match.
     * 
     * @param \forevermatt\mosaic\Match $otherMatch
     * @return boolean
     */
    public function isBetterMatchThan(Match $otherMatch)
    {
        if ($otherMatch === null) {
            return true;
        }
        
        $otherMatchDifference = $otherMatch->getDifference();
        if ($otherMatchDifference === null) {
            return ($this->difference !== null);
        } else {
            return ($this->difference < $otherMatchDifference);
        }
    }
    
    public function markSourceImageAsUsed()
    {
        $this->sourceImage->markAsUsed();
    }
    
    /**
     * Find out whether this Match's source Image is available to be used.
     * 
     * @return bool
     */
    public function isSourceImageAvailable()
    {
        //return true;
        return $this->sourceImage->isAvailable();
    }
}
