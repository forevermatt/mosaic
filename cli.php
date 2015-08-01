<?php

$startTime = time();

require_once __DIR__ . '/MosaicMaker.php';
require_once __DIR__ . '/Image.php';
require_once __DIR__ . '/ImageSlice.php';
require_once __DIR__ . '/Mosaic.php';
require_once __DIR__ . '/Match.php';

if ($argc < 3) {
    echo 'Usage: php ' . basename(__FILE__)
        . ' path/to/guide-image.jpg path/to/source/images/' . PHP_EOL;
    return;
}

$pathToGuideImage = $argv[1];
$pathsToSourceImages = glob(realpath($argv[2]) . '/*.*');

//die(var_dump(
//    $pathToGuideImage,
//    $pathsToSourceImages
//));

$mosaicFileName = forevermatt\mosaic\MosaicMaker::makeMosaic(
    $pathToGuideImage,
    $pathsToSourceImages
);
echo 'Saved mosaic as "' . $mosaicFileName . '".' . PHP_EOL;
echo '(Run time: ' . (time() - $startTime) . ' seconds)' . PHP_EOL;
