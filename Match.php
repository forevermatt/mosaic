<?php

namespace forevermatt\mosaic;

class Match
{
    protected $difference = null;
    protected $slice = null;
    protected $sourceImage = null;
    
    /**
     * Create a new Match object for handling data about how well a particular
     * guide image slice and a particular source image match each other.
     * 
     * @param Image $slice
     * @param Image $sourceImage
     * @param int $difference
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
    
    /**
     * Find out whether this Match is better (aka. - has less difference) than
     * the given Match.
     * 
     * @param \forevermatt\mosaic\Match $otherMatch
     * @return boolean
     */
    public function isBetterMatchThan(Match $otherMatch)
    {
        $otherMatchDifference = $otherMatch->getDifference();
        if ($otherMatchDifference === null) {
            return ($this->difference !== null);
        } else {
            return ($this->difference < $otherMatchDifference);
        }
    }
}
