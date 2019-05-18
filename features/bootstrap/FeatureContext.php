<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
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
    
    /** @var string */
    protected $pathToTopUpPhoto = __DIR__ . '/../test-images/up.jpg';
    
    /**
     * @Given I have an image where the top is up
     */
    public function iHaveAnImageWhereTheTopIsUp()
    {
        Assert::assertFileExists($this->pathToTopUpPhoto);
        $this->pathToImage = $this->pathToTopUpPhoto;
    }

    /**
     * @When I load the image
     */
    public function iLoadTheImage()
    {
        $this->loadedImage = new Image($this->pathToImage);
    }

    /**
     * @Then the top should be up
     */
    public function theTopShouldBeUp()
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
        $this->assertDimensionsMatch($imageResource1, $imageResource2);
        
        $desiredSize = 5;
        $smaller1 = self::resizeImageForTest($imageResource1, $desiredSize, $desiredSize);
        $smaller2 = self::resizeImageForTest($imageResource2, $desiredSize, $desiredSize);
        
        for ($x = 0; $x < $desiredSize; $x++) {
            for ($y = 0; $y < $desiredSize; $y++) {
                Assert::assertSame(
                    imagecolorat($smaller1, $x, $y),
                    imagecolorat($smaller2, $x, $y)
                );
            }
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
        
        Assert::assertSame($width1, $width2);
        Assert::assertSame($height1, $height2);
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
}
