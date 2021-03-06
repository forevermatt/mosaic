<?php

use Behat\Behat\Context\Context;
use forevermatt\mosaic\Image;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /** @var Image */
    protected $loadedImage;
    
    /** @var string */
    protected $pathToImage;
    
    /**
     * @Given I have an image where the top should be up
     */
    public function iHaveAnImageWhereTheTopShouldBeUp()
    {
        $this->pathToImage = $this->getPathToTestImage('top');
        Assert::assertFileExists($this->pathToImage);
    }
    
    protected function getPathToTestImage(string $sideThatShouldBeUp)
    {
        return __DIR__ . '/../test-images/' . $sideThatShouldBeUp . '.jpg';
    }

    /**
     * @When I load the image
     */
    public function iLoadTheImage()
    {
        $this->loadedImage = new Image($this->pathToImage);
    }

    /**
     * @Then the top should now be up
     */
    public function theTopShouldNowBeUp()
    {
        $this->assertTopIsUp($this->pathToImage, $this->loadedImage);
    }
    
    protected function assertTopIsUp(string $pathToImage, Image $loadedImage)
    {
        $imageWithTopUp = imagecreatefromjpeg($pathToImage);
        $correctedImage = $loadedImage->getImageResource();
        $this->assertImagesMatch($imageWithTopUp, $correctedImage);
    }
    
    protected function assertImagesMatch($imageResource1, $imageResource2)
    {
        try {
            $this->assertDimensionsMatch($imageResource1, $imageResource2);
            
            $desiredSize = 5;
            $smaller1 = self::resizeImageForTest($imageResource1, $desiredSize, $desiredSize);
            $smaller2 = self::resizeImageForTest($imageResource2, $desiredSize, $desiredSize);
            
            for ($x = 0; $x < $desiredSize; $x++) {
                for ($y = 0; $y < $desiredSize; $y++) {
                    Assert::assertSame(
                        imagecolorat($smaller1, $x, $y),
                        imagecolorat($smaller2, $x, $y),
                        sprintf('The pixels as (%s, %s) are different', $x, $y)
                    );
                }
            }
        } catch (\Exception $e) {
            $this->saveAsJpg($imageResource1, 'different-1.jpg');
            $this->saveAsJpg($imageResource2, 'different-2.jpg');
            throw $e;
        }
    }
    
    protected function assertDimensionsMatch($imageResource1, $imageResource2)
    {
        $width1 = imagesx($imageResource1);
        Assert::assertNotFalse($width1);
        $height1 = imagesy($imageResource1);
        Assert::assertNotFalse($height1);
        
        $width2 = imagesx($imageResource2);
        Assert::assertNotFalse($width2);
        $height2 = imagesy($imageResource2);
        Assert::assertNotFalse($height2);
        
        Assert::assertSame($width1, $width2, 'The images are different widths');
        Assert::assertSame($height1, $height2, 'The images are different heights');
    }
    
    protected static function resizeImageForTest($imageResource, $desiredWidth, $desiredHeight)
    {
        $resizedImageResource = imagecreatetruecolor($desiredWidth, $desiredHeight);

        $success = imagecopyresized(
            $resizedImageResource,
            $imageResource,
            0,
            0,
            0,
            0,
            $desiredWidth,
            $desiredHeight,
            imagesx($imageResource),
            imagesy($imageResource)
        );
        Assert::assertTrue($success, 'Failed to resize image for comparing / test');
        return $resizedImageResource;
    }
    
    protected function saveAsJpg($imageResource, $filename)
    {
        Assert::assertTrue(
            imagejpeg($imageResource, $filename, 95),
            'Failed to save image resource as ' . $filename
        );
        echo 'Saved image to ' . realpath($filename) . ' (' . $filename . ')' . PHP_EOL;
    }

    /**
     * @Given I have an image where the right side should be up
     */
    public function iHaveAnImageWhereTheRightSideShouldBeUp()
    {
        $this->pathToImage = $this->getPathToTestImage('right');
        Assert::assertFileExists($this->pathToImage);
    }

    /**
     * @Then the right side should now be up
     */
    public function theRightSideShouldNowBeUp()
    {
        $this->assertRightSideIsUp($this->pathToImage, $this->loadedImage);
    }
    
    protected function assertRightSideIsUp(string $pathToImage, Image $loadedImage)
    {
        $imageWithTopToTheRight = imagecreatefromjpeg($pathToImage);
        $imageWithRightSideUp = imagerotate($imageWithTopToTheRight, 90, 0);
        $correctedImage = $loadedImage->getImageResource();
        $this->assertImagesMatch($imageWithRightSideUp, $correctedImage);
    }

    /**
     * @Given I have an image where the bottom should be up
     */
    public function iHaveAnImageWhereTheBottomShouldBeUp()
    {
        $this->pathToImage = $this->getPathToTestImage('bottom');
        Assert::assertFileExists($this->pathToImage);
    }

    /**
     * @Then the bottom should now be up
     */
    public function theBottomShouldNowBeUp()
    {
        $this->assertBottomIsUp($this->pathToImage, $this->loadedImage);
    }

    /**
     * @Given I have an image where the left side should be up
     */
    public function iHaveAnImageWhereTheLeftSideShouldBeUp()
    {
        $this->pathToImage = $this->getPathToTestImage('left');
        Assert::assertFileExists($this->pathToImage);
    }

    /**
     * @Then the left side should now be up
     */
    public function theLeftSideShouldNowBeUp()
    {
        $this->assertLeftSideIsUp($this->pathToImage, $this->loadedImage);
    }
    
    protected function assertLeftSideIsUp(string $pathToImage, Image $loadedImage)
    {
        $imageWithTopToTheLeft = imagecreatefromjpeg($pathToImage);
        $imageWithLeftSideUp = imagerotate($imageWithTopToTheLeft, -90, 0);
        $correctedImage = $loadedImage->getImageResource();
        $this->assertImagesMatch($imageWithLeftSideUp, $correctedImage);
    }
}
