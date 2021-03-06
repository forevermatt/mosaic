<?php

namespace forevermatt\mosaic;

class Mosaic
{
    protected $image = null;
    private $guideImage = null;
    private $guideImageSlices = null;
    private $sourceImages = null;
    
    /**
     * Create a new Mosaic from the given sets of images.
     * 
     * @param GuideImage $guideImage The guide image.
     * @param SourceImage[] $sourceImages The array of Source Images.
     */
    public function __construct($guideImage, $sourceImages)
    {
        $this->guideImage = $guideImage;
        $this->sourceImages = $sourceImages;
        
        $numSourceImages = count($sourceImages);
        $maxNumSlices = min(
            3000,
            ($numSourceImages / 3)
        );
        $minNumSlices = min(
            2500,
            ($maxNumSlices - 50)
        );
        
        $this->guideImageSlices = $guideImage->slice(
            $maxNumSlices,
            $minNumSlices
        );
    }
    
    /**
     * Generate this Mosaic as an Image.
     * 
     * @return \forevermatt\mosaic\Image
     * @throws \Exception
     */
    public function generateImage()
    {
        // Make sure we actually have some images to work with.
        if (($this->guideImageSlices === null) ||
            (count($this->guideImageSlices) < 1)) {
            throw new \Exception(
                'We have no guide image slices, so we cannot '
                . 'generate the mosaic image.',
                1426676832
            );
        }
        if (($this->sourceImages === null) ||
            (count($this->sourceImages) < 1)) {
            throw new \Exception(
                'We have no source images, so we cannot generate '
                . 'the mosaic image.',
                1426676975
            );
        }
        
        $bestMatches = array();
        
        $tempCounter = 0;
        $progressMeterOne = new ProgressMeter();
        $numGuideImageSlices = count($this->guideImageSlices);
        $numSourceImages = count($this->sourceImages);
        $numCombinations = $numGuideImageSlices * $numSourceImages;

        // Compare each slice with each source image.
        $differenceDataBySlice = [];
        for ($sliceIndex = 0; $sliceIndex < $numGuideImageSlices; $sliceIndex++) {
            $slice = $this->guideImageSlices[$sliceIndex];
            $differenceFromSourceImages = [];
            for ($sourceImageIndex = 0; $sourceImageIndex < $numSourceImages; $sourceImageIndex++) {
                /* @var $sourceImage SourceImage */
                $sourceImage = $this->sourceImages[$sourceImageIndex];
                
                $differenceFromSourceImages[$sourceImageIndex] = $slice->compareWith($sourceImage);
                
                $progressMeterOne->showProgress(
                    'Comparing             (2/4)',
                    ++$tempCounter / $numCombinations
                );
            }
            
            // Sort the differences so that the best match is first in the array
            // without changing which key goes with which value.
            asort($differenceFromSourceImages);
            
            $differenceDataBySlice[$sliceIndex] = $differenceFromSourceImages;
        }
        
        // Now go through and select which source image to use for each slice.
        $bestMatchBySlice = array_map(
            function ($differenceFromSourceImages) {
                foreach($differenceFromSourceImages as $sourceImageIndex => $difference) {
                    return $difference;
                }
            },
            $differenceDataBySlice
        );
        
        // Sort that list to have the slices with the worse "best match" first.
        arsort($bestMatchBySlice);
        
        $tempCounter = 0;
        
        // Go through that list.
        foreach ($bestMatchBySlice as $sliceIndex => $lowestDifferenceValue) {
            $slice = $this->guideImageSlices[$sliceIndex];
            
            $differenceFromSourceImages = $differenceDataBySlice[$sliceIndex];
            foreach ($differenceFromSourceImages as $sourceImageIndex => $difference) {
                /* @var $sourceImage SourceImage */
                $sourceImage = $this->sourceImages[$sourceImageIndex];

                if ($sourceImage->isAvailable()) {
                    $sourceImage->markAsUsed();
                    $bestMatches[] = new Match($slice, $sourceImage, $difference);
                    break;
                }
            }

            $progressMeterOne->showProgress(
                'Getting matches       (3/4)',
                ++$tempCounter / $numGuideImageSlices
            );
        }
        
//        // Take the most accurate match we found and record it in our final
//        // list.
//        $finalMatchList = array();
//        $progressMeterTwo = new ProgressMeter();
//        $originalNumBNEMatches = count($bestNonExclusiveMatches);
//        while (count($bestNonExclusiveMatches) > 0) {
//            $bestMatch = $this->extractBestMatchFromList(
//                $bestNonExclusiveMatches
//            );
//            
//            if ($bestMatch->isSourceImageAvailable()) {
//                $finalMatchList[] = $bestMatch;
//                $bestMatch->markSourceImageAsUsed();
//                
//                $progressMeterTwo->showProgress(
//                    'Finding best final matches',
//                    ($originalNumBNEMatches - $bestNonExclusiveMatches) / $originalNumBNEMatches
//                );
//                
//                //// TEMP
//                //$bestMatch->getSlice()->saveAsJpg($tempCounter . '_slice.jpg');
//                //$bestMatch->getSourceImage()
//                //          ->getSizedImage(
//                //              $bestMatch->getSlice()->getWidth(),
//                //              $bestMatch->getSlice()->getHeight()
//                //          )
//                //          ->saveAsJpg($tempCounter . '_source-image.jpg');
//                //$tempCounter++;
//                
//            } else {
//                
//                // Find a new best match for that slice from among the
//                // remaining (i.e. - still unused) source images.
//                $newBestMatch = $this->getBestMatchForSlice(
//                    $bestMatch->getSlice(),
//                    $this->sourceImages
//                );
//                $bestNonExclusiveMatches[] = $newBestMatch;
//            }
//        }
        
        return $this->assembleImageFromMatches($bestMatches);
    }
    
    /**
     * Assemble the final mosaic Image from the list of Matches.
     * 
     * @param Match[] $matches
     * @return Image
     * @throws \Exception
     */
    protected function assembleImageFromMatches($matches)
    {
        // Define how much bigger (than the guide image) to make the mosaic.
        $multiplier = 2;
        
        $imageResource = imagecreatetruecolor(
            $this->guideImage->getWidth() * $multiplier,
            $this->guideImage->getHeight() * $multiplier
        );
        
        $tempCounter = 0;
        $progressMeter = new ProgressMeter();
        $numMatches = count($matches);
        
        foreach ($matches as $match) {
            /* @var $match Match */
            
            $guideImageSlice = $match->getSlice();
            $imageToInsert = $match->getSourceImage();
            $success = imagecopyresampled(
                $imageResource,
                $imageToInsert->getImageResource(),
                $guideImageSlice->xOffsetInParent * $multiplier,
                $guideImageSlice->yOffsetInParent * $multiplier,
                0,
                0,
                $guideImageSlice->getWidth() * $multiplier,
                $guideImageSlice->getHeight() * $multiplier,
                $imageToInsert->getWidth(),
                $imageToInsert->getHeight()
            );
        
            // Stop if something went wrong.
            if ( ! $success) {
                throw new \Exception(
                    'Failed to insert a source image into the final mosaic.',
                    1435207513
                );
            }
            
            $progressMeter->showProgress(
                'Assembling mosaic     (4/4)',
                ++$tempCounter / $numMatches
            );
            
            //$tempImage = new Image();
            //$tempImage->setImageResource($imageResource);
            //$tempImage->saveAsJpg('Step-' . ++$tempCounter . '.jpg');
        }
        
        $image = new Image();
        $image->setImageResource($imageResource);
        return $image;
    }

    /**
     * Get this mosaic as an image, generating it if necessary.
     * 
     * @return \forevermatt\mosaic\Image
     */
    public function getAsImage()
    {
        if ($this->image === null) {
            $this->image = $this->generateImage();
        }
        return $this->image;
    }
    
    /**
     * Find the source image that best matches the given guide image slice. Note
     * that only images that are still flagged as available will be checked.
     * 
     * @param Image $slice The guide image slice.
     * @param Image[] $sourceImages The array of source Images.
     * @return Match
     */
    public function getBestMatchForSlice(ImageSlice $slice, $sourceImages)
    {
        // Define what precision level to check initially.
        $precision = 3;
        
        $bestMatches = $this->getBestMatchesForSliceAtPrecisionLevel(
            $slice,
            $sourceImages,
            $precision
        );
        
        //while ((++$precision <= 4) && (count($bestMatches) > 1)) {
        //    $bestMatches = $this->getBestMatchesForSliceAtPrecisionLevel(
        //        $slice,
        //        $sourceImages,
        //        $precision
        //    );
        //}
        
        return $bestMatches[0];
    }
    
    /**
     * Get the list of Matches indicating which source Image(s) best match the
     * given guide image slice.
     * 
     * @param Image $slice The guide image slice.
     * @param Image[] $sourceImages The list of source Images.
     * @param int $precision The desired precision level for the comparison.
     * @return Match[] The list of Matches (guaranteed to contain at least one,
     *     or throw an Exception).
     */
    public function getBestMatchesForSliceAtPrecisionLevel(
        $slice,
        $sourceImages,
        $precision
    ) {
        // Make sure we were given a slice image.
        if ( ! ($slice instanceof Image)) {
            throw new \InvalidArgumentException(
                'No guide image slice was provided, so we cannot find the '
                . 'source images that best match it.',
                1434902367
            );
        }
        
        // Make sure we were given at least one source Image.
        if (count($sourceImages) < 1) {
            throw new \InvalidArgumentException(
                'No source images were provided, so we cannot find the ones'
                . 'that best match the given guide image slice.',
                1434902467
            );
        }
        
        // Assemble the list of Matches for which source Images best match the
        // given slice Image at a particular precision level.
        $smallestDifference = null;
        $bestMatches = array();
        foreach ($sourceImages as $sourceImage) {
            
            if ( ! ($sourceImage instanceof Image)) {
                throw new \InvalidArgumentException(
                    'The array of source images contained something that was '
                    . 'not an Image.',
                    1435210748
                );
            }

            // If this image is no longer available, skip it.
            if ( ! $sourceImage->isAvailable()) {
                continue;
            }
            
            $difference = $slice->compareWith($sourceImage, $precision);

            // If this is our first comparison for this slice
            //    OR
            // if this matches better than our previous best match for this
            // slice...
            if (($smallestDifference === null) ||
                ($difference < $smallestDifference)) {

                // Record that this is the best Match for this slice so far.
                $smallestDifference = $difference;
                $bestMatches = [];
                $bestMatches[] = new Match($slice, $sourceImage, $difference);
            }
            // OR, if this matches exactly as well as our previous best Match...
            elseif ($difference === $smallestDifference) {
                
                // Add it to our list.
                $bestMatches[] = new Match($slice, $sourceImage, $difference);
            }
        }
        
        // If we didn't end up with any, something went wrong.
        if (count($bestMatches) < 1) {
            throw new \Exception(
                'Oops! There were not any remaining source images available.',
                1435204675
            );
        }
        
        // Return the resulting list of Matches (which should contain at least
        // one).
        return $bestMatches;
    }
    
    /**
     * Extract the best Match from the given list.
     * 
     * @param Match[] $matchList The list of Matches. NOTE: This will modify the
     *     given array.
     * @return Match
     */
    public function extractBestMatchFromList(&$matchList)
    {
        //echo 'extractBestMatchFromList(' . count($matchList) . ')' . PHP_EOL; // TEMP
        
        $bestMatch = $matchList[0];
        $bestMatchIndex = 0;
        for ($i = 1; $i < count($matchList); $i++) {
            if ($matchList[$i]->isBetterMatchThan($bestMatch)) {

                //echo sprintf(
                //    ' (%s is a better match than %s)%s',
                //    $matchList[$i]->getSourceImage()->getFileName(),
                //    $bestMatch->getSourceImage()->getFileName(),
                //    PHP_EOL
                //);
                
                $bestMatch = $matchList[$i];
                $bestMatchIndex = $i;
            }
        }
        
        array_splice($matchList,$bestMatchIndex, 1);
        
        //echo ' -> ' . count($matchList) . PHP_EOL; // TEMP
        
        return $bestMatch;
    }
    
    /**
     * Generate and save this mosaic as an image file at the specified path.
     * 
     * @param string $pathAndFilename The path and filename for the new image.
     */
    public function saveAs($pathAndFilename)
    {
        $image = $this->getAsImage();
        $image->saveAsJpg($pathAndFilename);
    }
}
