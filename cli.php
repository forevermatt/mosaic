<?php

$startTime = time();

require_once __DIR__ . '/vendor/autoload.php';

if ($argc < 3) {
    echo 'Usage: php ' . basename(__FILE__)
        . ' path/to/guide-image.jpg path/to/source/images/' . PHP_EOL;
    return;
}

$mosaicFileName = forevermatt\mosaic\MosaicMaker::makeMosaic(
    $argv[1],
    $argv[2]
);
echo 'Saved mosaic as "' . $mosaicFileName . '".' . PHP_EOL;
echo '(Run time: ' . number_format((time() - $startTime) / 60, 2) . ' minutes)'
    . PHP_EOL;
