<?php

namespace forevermatt\mosaic;

class ComparableImage extends Image
{
    const SIGNATURE_PRECISION = 3;
    
    protected $signature = null;
    
    /**
     * Get the actual color difference between the two given signature images.
     * 
     * @param Image $signature1 The first image's signature.
     * @param Image $signature2 The second image's signature.
     * @return int The difference between the two signatures Images.
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
                $color1 = imagecolorsforindex(
                    $imageResource1,
                    imagecolorat($imageResource1, $x, $y)
                );
                $color2 = imagecolorsforindex(
                    $imageResource2,
                    imagecolorat($imageResource2, $x, $y)
                );
                $pixelDifference = abs($color1['red'] - $color2['red'])
                    + abs($color1['green'] - $color2['green'])
                    + abs($color1['blue'] - $color2['blue']);
                $totalDifference += $pixelDifference;
            }
        }

        return $totalDifference;
    }
    
    /**
     * Compare this image with the given image using signatures (i.e. downsized
     * copies of the each image).
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
