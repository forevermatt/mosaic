<?php

namespace forevermatt\mosaic;

class ComparableImage extends Image
{
    const SIGNATURE_PRECISION = 3;
    
    protected $signature = null;
    
    /**
     * Calculate how similar this Image is with the given Image. If the Images
     * are different sizes, the given Image will be resized to match this one's
     * dimensions, but otherwise it will be a full-size, pixel-for-pixel
     * comparison.
     * 
     * @param Image $otherImage The other image. NOTE: Its image resource will
     *     be modified if the sizes don't match.
     * @return float The similarity between the two images as a percentage,
     *     where 1.0 (100%) means they are identical identical and 0.0 (0%)
     *     means they have no similarity.
     */
    public function calculateSimilarityTo($otherImage)
    {
        if ( ! $this->hasSameDimensionsAs($otherImage)) {
            $resizedImageResource = Image::resizeImageResource(
                $otherImage->getImageResource(),
                $this->getWidth(),
                $this->getHeight()
            );
            $otherImage->setImageResource($resizedImageResource);
        }
        
        $absoluteDifference = $this->getAbsoluteDifference($otherImage);
        $numPixels = $this->getWidth() * $this->getHeight();
        $maxPossibleDifference = Image::COLOR_MAX_VALUE *
            Image::COLOR_VALUES_PER_PIXEL * $numPixels;
        $differenceAsPercentage = $absoluteDifference / $maxPossibleDifference;
        $similarity = 1 - $differenceAsPercentage;
        return $similarity;
    }
    
    /**
     * Get the actual color difference between the two given signature images.
     * 
     * NOTE: The bit-shifting technique for getting RGB values from pixels only
     * works for true-color image resources (not GIF's, for example). See
     * "http://php.net/manual/en/function.imagecolorsforindex.php#84969" for
     * details.
     * 
     * @param Image $signature1 The first image's signature.
     * @param Image $signature2 The second image's signature.
     * @return int|float The difference between the two signatures Images. See
     *     the "Integer overflow" section of the webpage at
     *     "http://php.net/manual/en/language.types.integer.php" for details
     *     about when this will return a float.
     */
    protected function getAbsoluteDifference($otherSignature)
    {
        // Get the dimensions of the two signature images.
        $width1 = $this->getWidth();
        $height1 = $this->getHeight();
        $width2 = $otherSignature->getWidth();
        $height2 = $otherSignature->getHeight();

        // If the two signature images are different, stop.
        if (($width1 !== $width2) || ($height1 !== $height2)) {
            throw new \Exception(
                'Cannot compare signature images of different precision '
                . 'levels.',
                1425386364
            );
        }

        $imageResource1 = $this->getImageResource();
        $imageResource2 = $otherSignature->getImageResource();

        $totalDifference = 0;

        // For each pixel in the images, add up the color differences.
        //
        // @TODO: Optimize this to speed it up.
        //
        for ($x = 0; $x < $width1; $x++) {
            for ($y = 0; $y < $height1; $y++) {
                $rgba1 = imagecolorat($imageResource1, $x, $y);
                $r1 = ($rgba1 >> 16) & 0xFF;
                $g1 = ($rgba1 >> 8) & 0xFF;
                $b1 = $rgba1 & 0xFF;
                $rgba2 = imagecolorat($imageResource2, $x, $y);
                $r2 = ($rgba2 >> 16) & 0xFF;
                $g2 = ($rgba2 >> 8) & 0xFF;
                $b2 = $rgba2 & 0xFF;
                $pixelDifference = abs($r1 - $r2)
                                 + abs($g1 - $g2)
                                 + abs($b1 - $b2);
                $totalDifference += $pixelDifference;
            }
        }

        return $totalDifference;
    }
    
    /**
     * Compare this image with the given image using signatures (i.e. downsized
     * copies of each image).
     * 
     * @param ComparableImage $otherImage The Image to compare this Image with.
     * @return int The difference between the two signatures.
     */
    public function compareWith($otherImage)
    {
        // Get this image's signature.
        $thisSignature = $this->getSignature();
        
        // Get the other image's signature.
        $otherSignature = $otherImage->getSignature();
        
        // Return the difference of the two signature images.
        return $thisSignature->getAbsoluteDifference($otherSignature);
    }
    
    protected function generateSignature()
    {
        // Resize the image resource down to the size necessary for the
        // specified precision level.
        $this->signature = $this->getSizedImage(
            self::SIGNATURE_PRECISION,
            self::SIGNATURE_PRECISION
        );
    }
    
    /**
     * Get a "signature" of this Image by downsizing it to a very few pixels.
     * 
     * @return Image The signature image (to use for comparing).
     */
    public function getSignature()
    {
        // If we haven't yet calculated the signature at the indicated
        // precision, do so.
        if ($this->signature === null) {
            $this->generateSignature();
        }
        return $this->signature;
    }
    
}
