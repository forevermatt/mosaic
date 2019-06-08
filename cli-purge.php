<?php

$startTime = time();
require_once __DIR__ . '/vendor/autoload.php';

use forevermatt\mosaic\MosaicMaker;
use forevermatt\mosaic\ProgressMeter;

if ($argc < 2) {
    echo 'Usage: php ' . basename(__FILE__)
        . ' path/to/source/images/ '
        . '[path/to/more/source/images [...]]' . PHP_EOL;
    return;
}

$pathsToSourceImagesFolders = array();
for ($i = 1; $i < $argc; $i++) {
    $pathsToSourceImagesFolders[] = $argv[$i];
}

MosaicMaker::purgeRotatedImagesFromCache(
    $pathsToSourceImagesFolders
);
echo '(Run time: ' . ProgressMeter::getDurationAsString(time() - $startTime) . ')' . PHP_EOL;
