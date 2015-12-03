<?php
namespace forevermatt\mosaic;

class GuideImage extends Image
{
    /**
     * Extract a slice from this image. Any necessary rounding to end up with
     * whole pixels will be done as late in the calculations as possible.
     * 
     * @param float $xStart The horizontal offset (from the left edge) where the
     *     slice should start, in pixels.
     * @param float $xStop The horizontal offset (from the left edge) where the
     *     slice should stop, in pixels.
     * @param float $yStart The vertical offset (below the top edge) where the
     *     slice should start, in pixels.
     * @param float $yStop The vertical offset (below the top edge) where the
     *     slice should stop, in pixels.
     * @return ImageSlice
     * @throws \Exception
     */
    public function getSlice(
        $xStart,
        $xStop,
        $yStart,
        $yStop
    ) {
        // Calculate the target dimensions.
        $xStartRounded = round($xStart);
        $yStartRounded = round($yStart);
        $width = round($xStop) - $xStartRounded;
        $height = round($yStop) - $yStartRounded;
        
        // Create the image resource into which the slice will be put.
        $sliceImageResource = imagecreatetruecolor($width, $height);
        $success = imagecopy(
            $sliceImageResource,
            $this->getImageResource(),
            0,
            0,
            $xStartRounded,
            $yStartRounded,
            $width,
            $height
        );
        
        // Stop if something went wrong.
        if ( ! $success) {
            throw new \Exception(
                'Failed to extract a slice from the image.',
                1424780302
            );
        }
        
        // Create an Image object from that image resource.
        $slice = new ImageSlice();
        $slice->setImageResource($sliceImageResource);
        $slice->xOffsetInParent = $xStartRounded;
        $slice->yOffsetInParent = $yStartRounded;
        
        // Otherwise return the extracted image slice.
        return $slice;
    }
    
    /**
     * Slice this Image into no more than the specified number of slices. The
     * slices will (essentially) have the same aspect ratio as the image (within
     * a pixel each direction).
     * 
     * @param int $maxNumSlices The (inclusive) maximum number of slices.
     * @param int $minNumSlices The (inclusive) minimum number of slices.
     * @return ImageSlice[] An array of ImageSlices.
     */
    public function slice($maxNumSlices, $minNumSlices)
    {
        // Get the dimensions the image.
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();
        
        // Calculate how to slice up the image.
        $sliceGrid = new SliceGrid(
            $maxNumSlices,
            $minNumSlices,
            ($imageWidth / $imageHeight)
        );
        
        // Get the largest square number that's no bigger than our max-slice
        // limit.
        $numSlicesPerSide = 1;
        $maxNumSlicesPerSide = (int)floor(sqrt($maxNumSlices));
        while ($numSlicesPerSide < $maxNumSlicesPerSide) {
            $numSlicesPerSide += 1;
        }
        
        // Figure out the number of pixels (horizontal and vertical) in each
        // slice.
        $numColumns = $sliceGrid->getNumColumns();
        $numRows = $sliceGrid->getNumRows();
        $hPixelsPerSlice = $imageWidth / $numColumns;
        $vPixelsPerSlice = $imageHeight / $numRows;
        
        // Extract all of the slices of the image.
        $slices = array();
        for ($vSliceOffset = 0; $vSliceOffset < $numRows; $vSliceOffset++) {
            for ($hSliceOffset = 0; $hSliceOffset < $numColumns; $hSliceOffset++) {
                
                // Figure out where this slice will start.
                $xStart = $hSliceOffset * $hPixelsPerSlice;
                $yStart = $vSliceOffset * $vPixelsPerSlice;
                
                // Extract the slice from the full image.
                $slices[] = $this->getSlice(
                    $xStart,
                    ($xStart + $hPixelsPerSlice),
                    $yStart,
                    ($yStart + $vPixelsPerSlice)
                );
            }
        }
        
        // Return the resulting array of Images sliced from the original.
        return $slices;
    }
}
