<?php

$startTime = time();

require_once __DIR__ . '/vendor/autoload.php';

if ($argc < 3) {
    echo 'Usage: php ' . basename(__FILE__)
        . ' path/to/guide-image.jpg path/to/source/images/' . PHP_EOL;
    return;
}

$pathToGuideImage = $argv[1];
$pathsToSourceImages = array_merge(
    glob(realpath($argv[2]) . '/*.*'),   // = the specified folder.
    glob(realpath($argv[2]) . '/**/*.*') // = any immediate subfolders.
);

$mosaicFileName = forevermatt\mosaic\MosaicMaker::makeMosaic(
    $pathToGuideImage,
    $pathsToSourceImages
);
echo 'Saved mosaic as "' . $mosaicFileName . '".' . PHP_EOL;
echo '(Run time: ' . (time() - $startTime) . ' seconds)' . PHP_EOL;
