<?php

namespace forevermatt\mosaic;

class ProgressMeter
{
    /**
     * Show the current progress.
     * 
     * @param string $taskName The name of the current task.
     * @param float $percentComplete The current progress, as a floating point
     *     number between 0.0 and 1.0 (inclusive).
     */
    public static function showProgress($taskName, $percentComplete)
    {
        echo sprintf(
            '%s: %01.2f%%' . PHP_EOL,
            $taskName,
            $percentComplete * 100
        );
    }
}
