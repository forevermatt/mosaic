<?php

namespace forevermatt\mosaic;

class ProgressMeter
{
    private $lastUpdateAt = null;
    private $lastUpdatePercent = null;
    private $currentRate = null;
    
    public function __construct()
    {
        $now = microtime(true);
        $this->lastUpdateAt = $now;
        $this->startedAt = $now;
    }
    
    protected function calculateRemainingSeconds($currentRate, $currentProgress)
    {
        return (1 - $currentProgress) / $currentRate;
    }
    
    /**
     * Get a more friendly display of a duration.
     * 
     * @param float $numSeconds The duration in seconds.
     */
    public static function getDurationAsString($seconds)
    {
        return sprintf(
            '%02.0f:%02.0f',
            floor($seconds / 60),
            ($seconds % 60)
        );
    }
    
    protected function getElapsedTime($now)
    {
        return self::getDurationAsString($now - $this->startedAt);
    }
    
    protected function getRemainingTime(
        $secondsSinceLastUpdate,
        $percentComplete
    ) {
        if ($this->lastUpdatePercent !== null) {
            $progressSinceLastUpdate = $percentComplete - $this->lastUpdatePercent;
            $rateSinceLastUpdate = $progressSinceLastUpdate / $secondsSinceLastUpdate;
            if ($this->currentRate === null) {
                $this->currentRate = $rateSinceLastUpdate;
            }
            $this->currentRate = $this->getNewCurrentRate(
                $rateSinceLastUpdate
            );
            $remainingSeconds = $this->calculateRemainingSeconds(
                $this->currentRate,
                $percentComplete
            );
            $remainingTime = sprintf(
                ',  Remaining time: %s',
                self::getDurationAsString($remainingSeconds)
            );
        } else {
            $remainingTime = '';
        }
        return $remainingTime;
    }
    
    protected function getNewCurrentRate($rateSinceLastUpdate)
    {
        return (0.85 * $this->currentRate) + (0.15 * $rateSinceLastUpdate);
    }
    
    /**
     * Show the current progress.
     * 
     * @param string $taskName The name of the current task.
     * @param float $percentComplete The current progress, as a floating point
     *     number between 0.0 and 1.0 (inclusive).
     */
    public function showProgress($taskName, $percentComplete)
    {
        $now = microtime(true);
        $secondsSinceLastUpdate = $now - $this->lastUpdateAt;
        if ($secondsSinceLastUpdate >= 0.999) {
            $elapsedTime = $this->getElapsedTime($now);
            $remainingTime = $this->getRemainingTime(
                $secondsSinceLastUpdate,
                $percentComplete
            );

            echo sprintf(
                '%s: %\' 6.2f%%,  Elapsed time: %s%s' . PHP_EOL,
                $taskName,
                $percentComplete * 100,
                $elapsedTime,
                $remainingTime
            );

            $this->lastUpdateAt = $now;
            $this->lastUpdatePercent = $percentComplete;
        }
    }
}
