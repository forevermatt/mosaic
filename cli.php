<?php

$startTime = time();
require_once __DIR__ . '/vendor/autoload.php';

use forevermatt\mosaic\MosaicMaker;
use forevermatt\mosaic\ProgressMeter;

if ($argc < 3) {
    echo 'Usage: php ' . basename(__FILE__)
        . ' path/to/guide-image.jpg path/to/source/images/' . PHP_EOL;
    return;
}

$pathToGuideImage = $argv[1];

$pathsToSourceImagesFolders = array();
for ($i = 2; $i < $argc; $i++) {
    $pathsToSourceImagesFolders[] = $argv[$i];
}

$mosaicFileName = MosaicMaker::makeMosaic(
    $pathToGuideImage,
    $pathsToSourceImagesFolders
);
echo 'Saved mosaic as "' . $mosaicFileName . '".' . PHP_EOL;
echo '(Run time: ' . ProgressMeter::getDurationAsString(time() - $startTime) . ')'
    . PHP_EOL;
