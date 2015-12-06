<?php
namespace forevermatt\mosaic;

class SliceGrid
{
    const DEFAULT_DESIRED_ASPECT_RATIO = 1.3333333333333;
    const FLOAT_MAX_ACCURATE_DECIMAL_DIGITS = 10;
    
    private $numColumns = null;
    private $numRows = null;
    
    /**
     * Constructor.
     * 
     * @param int $maxSlices The (inclusive) maximum number of slices.
     * @param int $minSlices The (inclusive) minimum number of slices.
     * @param float $guideImageAspectRatio The aspect ratio of the guide image.
     * @param float $desiredSliceAspectRatio (Optional) The desired aspect ratio
     *     for the slices. Defaults to 4/3 (width/height).
     */
    public function __construct(
        $maxSlices,
        $minSlices,
        $guideImageAspectRatio,
        $desiredSliceAspectRatio = self::DEFAULT_DESIRED_ASPECT_RATIO
    ) {
        $this->calculateColumnsAndRows(
            $maxSlices,
            $minSlices,
            $guideImageAspectRatio,
            $desiredSliceAspectRatio
        );
    }
    
    protected function calculateColumnsAndRows(
        $maxSlices,
        $minSlices,
        $guideImageAspectRatio,
        $desiredSliceAspectRatio = self::DEFAULT_DESIRED_ASPECT_RATIO
    ) {
        if ($maxSlices < $minSlices) {
            throw new \InvalidArgumentException(
                'The maximum number of slices must not be less than the '
                . 'minimum number of slices.',
                1440671188
            );
        }
        
        $minNumColumns = (int)floor(
            round(
                sqrt(
                    ($minSlices / $guideImageAspectRatio) / $desiredSliceAspectRatio
                ) * $guideImageAspectRatio,
                self::FLOAT_MAX_ACCURATE_DECIMAL_DIGITS
            )
        );
        
        $maxNumColumns = (int)floor(
            round(
                sqrt(
                    ($maxSlices / $guideImageAspectRatio) / $desiredSliceAspectRatio
                ) * $guideImageAspectRatio,
                self::FLOAT_MAX_ACCURATE_DECIMAL_DIGITS
            )
        );
        
        $numColumns = null;
        $numRows = null;
        $bestAccuracy = null;
        
        for ($x = $maxNumColumns; $x >= $minNumColumns; $x--) {
            
            $y = $x * $desiredSliceAspectRatio / $guideImageAspectRatio;
            
            // Figure out how far away from a whole number y was, rounding
            // slightly to correct for inaccuracies of floating point numbers.
            $yLeftoverFraction = round(
                $y - floor($y),
                self::FLOAT_MAX_ACCURATE_DECIMAL_DIGITS
            );
            
            // Figure out how close we were (either above or below) to having
            // the number of rows (y) come out as a whole number. NOTE: Smaller
            // accuracy values are better.
            $accuracyOfFit = min(
                $yLeftoverFraction,
                abs(1 - $yLeftoverFraction)
            );
            
            if (($bestAccuracy === null) || ($accuracyOfFit < $bestAccuracy)) {
                $bestAccuracy = $accuracyOfFit;
                $numColumns = $x;
                $numRows = round($y);
                
                // If it was a perfect fit, stop trying to find a better fit.
                if ($bestAccuracy === 0) {
                    break;
                }
            }
        }
        
        if ($bestAccuracy === null) {
            throw new \Exception(
                'Unable to find a good slice grid for those limitations.',
                1440671996
            );
        }
        
        $this->numColumns = $numColumns;
        $this->numRows = $numRows;
    }
    
    public function getNumColumns()
    {
        return $this->numColumns;
    }
    
    public function getNumRows()
    {
        return $this->numRows;
    }
}
