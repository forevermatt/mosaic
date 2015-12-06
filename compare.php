<?php

$startTime = time();
require_once __DIR__ . '/vendor/autoload.php';

use forevermatt\mosaic\MosaicMaker;
use forevermatt\mosaic\ProgressMeter;

if ($argc < 3) {
    echo 'Usage: php ' . basename(__FILE__)
        . ' path/to/image-1.jpg path/to/image-2.jpg' . PHP_EOL;
    return;
}

$pathToImage1 = $argv[1];
$pathToImage2 = $argv[2];

$similarity = MosaicMaker::calculateSimilarity($pathToImage1, $pathToImage2);
echo sprintf(
    'Similarity between "%s" and "%s": %.2f%%' . PHP_EOL,
    basename($pathToImage1),
    basename($pathToImage2),
    ($similarity * 100)
);
