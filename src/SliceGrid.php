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
        //echo PHP_EOL;
        //echo 'maxSlices: ' . $maxSlices . PHP_EOL;
        //echo 'minSlices: ' . $minSlices . PHP_EOL;
        //echo 'guideImageAspectRatio: ' . $guideImageAspectRatio . PHP_EOL;
        //echo 'desiredSliceAspectRatio: ' . $desiredSliceAspectRatio . PHP_EOL;
        
        if ($maxSlices < $minSlices) {
            throw new \InvalidArgumentException(
                'The maximum number of slices must not be less than the '
                . 'minimum number of slices.',
                1440671188
            );
        }
        
        $minNumColumns = (int)ceil(
            round(
                sqrt(
                    ($minSlices / $guideImageAspectRatio) / $desiredSliceAspectRatio
                ) * $guideImageAspectRatio,
                self::FLOAT_MAX_ACCURATE_DECIMAL_DIGITS
            )
        );
        
        //echo 'sqrt('
        //   .     '($minSlices / $guideImageAspectRatio) / $desiredSliceAspectRatio'
        //   . ') * $guideImageAspectRatio' . PHP_EOL;
        //echo 'sqrt('
        //   .     '(' . $minSlices . ' / ' . $guideImageAspectRatio . ') / ' . $desiredSliceAspectRatio
        //   . ') * ' . $guideImageAspectRatio . PHP_EOL;
        //echo 'sqrt('
        //   .     '(' . $minSlices / $guideImageAspectRatio . ') / ' . $desiredSliceAspectRatio
        //   . ') * ' . $guideImageAspectRatio . PHP_EOL;
        //echo 'sqrt('
        //   .     '(' . $minSlices / $guideImageAspectRatio . ') / ' . $desiredSliceAspectRatio
        //   . ') * ' . $guideImageAspectRatio . PHP_EOL;
        //echo 'sqrt('
        //   .     ( $minSlices / $guideImageAspectRatio ) / $desiredSliceAspectRatio
        //   . ') * ' . $guideImageAspectRatio . PHP_EOL;
        //echo sqrt( ( $minSlices / $guideImageAspectRatio ) / $desiredSliceAspectRatio )
        //   . ' * ' . $guideImageAspectRatio . PHP_EOL;
        //echo sqrt( ( $minSlices / $guideImageAspectRatio ) / $desiredSliceAspectRatio )
        //     * $guideImageAspectRatio . PHP_EOL;
        //echo 'minNumColumns: ' . $minNumColumns . PHP_EOL;
        
        $maxNumColumns = (int)floor(
            round(
                sqrt(
                    ($maxSlices / $guideImageAspectRatio) / $desiredSliceAspectRatio
                ) * $guideImageAspectRatio,
                self::FLOAT_MAX_ACCURATE_DECIMAL_DIGITS
            )
        );
        
        //echo 'maxNumColumns: ' . $maxNumColumns . PHP_EOL;
        
        $numColumns = null;
        $numRows = null;
        $bestAccuracy = null;
        
        //echo 'x = $maxNumColumns' . PHP_EOL;
        //echo 'x = ' . $maxNumColumns . PHP_EOL;
        //echo '(x >= minNumColumns): ' . var_export(floor($maxNumColumns) >= $minNumColumns, true) . PHP_EOL;
        //echo '(' . floor($maxNumColumns) . ' >= ' . $minNumColumns . '): ' . var_export(floor($maxNumColumns) >= $minNumColumns, true) . PHP_EOL;
        //echo '(' . (int)floor($maxNumColumns) . ' >= ' . (int)$minNumColumns . '): ' . var_export(((int)floor($maxNumColumns)) >= ((int)$minNumColumns), true) . PHP_EOL;
        
        for ($x = $maxNumColumns; $x >= $minNumColumns; $x--) {
            
            $y = $x * $desiredSliceAspectRatio / $guideImageAspectRatio;
            
            //echo 'y: ' . $y . PHP_EOL;
            
            // Figure out how far away from a whole number y was, rounding
            // slightly to correct for inaccuracies of floating point numbers.
            $yLeftoverFraction = round(
                $y - floor($y),
                self::FLOAT_MAX_ACCURATE_DECIMAL_DIGITS
            );
            
            //echo 'yLeftoverFraction: ' . $yLeftoverFraction . PHP_EOL;
            
            // Figure out how close we were (either above or below) to having
            // the number of rows (y) come out as a whole number.
            $accuracyOfFit = min(
                $yLeftoverFraction,
                abs(1 - $yLeftoverFraction)
            );
            
            //echo 'accuracyOfFit: ' . $accuracyOfFit . PHP_EOL;
            
            if (($bestAccuracy === null) || ($accuracyOfFit < $bestAccuracy)) {
                $bestAccuracy = $accuracyOfFit;
                $numColumns = $x;
                $numRows = round($y);
                
                //echo '(best fit so far)' . PHP_EOL;
                
                // If it was a perfect fit, stop trying to find a better fit.
                if ($bestAccuracy === 0) {
                    
                    //echo '(prefect fit)' . PHP_EOL;
                    
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
