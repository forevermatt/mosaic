<?php

namespace forevermatt\mosaic;

class ProgressMeter
{
    private $lastUpdateAt = null;
    private $lastUpdatePercent = null;
    private $currentRate = 0;
    
    public function __construct()
    {
        $this->startedAt = microtime(true);
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
    
    protected function getNewCurrentRate($rateSinceLastUpdate)
    {
        return (0.99 * $this->currentRate) + (0.01 * $rateSinceLastUpdate);
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
        if (($this->lastUpdateAt !== null) && ($this->lastUpdatePercent)) {
            $secondsSinceLastUpdate = microtime(true) - $this->lastUpdateAt;
            $progressSinceLastUpdate = $percentComplete - $this->lastUpdatePercent;
            $this->currentRate = $this->getNewCurrentRate(
                $progressSinceLastUpdate / $secondsSinceLastUpdate
            );
            $remainingSeconds = $this->calculateRemainingSeconds(
                $this->currentRate,
                $percentComplete
            );
            $remainingTime = sprintf(
                ', Remaining time: %s',
                self::getDurationAsString($remainingSeconds)
            );
        } else {
            $remainingTime = '';
        }
        
        $elapsedTime = self::getDurationAsString(microtime(true) - $this->startedAt);
        
        echo sprintf(
            '%s: %01.2f%%, Elapsed time: %s%s' . PHP_EOL,
            $taskName,
            $percentComplete * 100,
            $elapsedTime,
            $remainingTime
        );
        $this->lastUpdateAt = microtime(true);
        $this->lastUpdatePercent = $percentComplete;
    }
}
