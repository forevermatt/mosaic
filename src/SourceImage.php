<?php

namespace forevermatt\mosaic;

class SourceImage extends Image
{
    protected function getFileNameHash()
    {
        return md5($this->getfileName());
    }
    
//    protected function getImageResourceFromJpgFile($pathToJpg)
//    {
//        $imageResource = null;
//        try {
//            $exifThumbnail = exif_thumbnail($pathToJpg);
//            if ($exifThumbnail !== false) {
//                $imageResource = imagecreatefromstring($exifThumbnail);
//            }
//        } catch (\Exception $e) {
//            echo sprintf(
//                'Note: Unable to read thumbnail from "%s".%s',
//                $pathToJpg,
//                PHP_EOL
//            );
//        }
//        
//        if (! $imageResource) {
//            $imageResource = imagecreatefromjpeg($pathToJpg);
//        }
//        
//        return $imageResource;
//    }
    
    public function loadImage($pathToImage)
    {
        $imageResource = parent::loadImage($pathToImage);
        
        // TEMP
        $tempCopyPath = sprintf(
            'temp/%sx%s_%s.jpg',
            \imagesx($imageResource),
            \imagesy($imageResource),
            $this->getFileNameHash()
        );
        if (! file_exists($tempCopyPath)) {
            $this->saveAsJpg(
                $tempCopyPath,
                95,
                $imageResource
            );
        }
    }
}
