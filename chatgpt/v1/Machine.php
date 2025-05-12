<?php


class Machine
{
    private $duration;
    private $busy = false;
    private $timeRemaining = 0;
    private $currentSet = null;

    public function __construct(int $duration) {
        $this->duration = $duration;
    }

    public function isBusy(): bool {
        return $this->busy;
    }

    public function assign(Set $set, int $currentTime, string $step) {
        $this->busy = true;
        $this->timeRemaining = $this->duration;
        $this->currentSet = $set;
        echo "Minute {$currentTime}: Set {$set->id} starts {$step}\n";
    }

    public function tick(): ?Set {
        if (!$this->busy) {
            return null;
        }
        $this->timeRemaining--;
        if ($this->timeRemaining <= 0) {
            $finishedSet = $this->currentSet;
            $stepDone = $finishedSet->getStepName();
            echo "Minute {$finishedSet->simulatorTime}: completed {$stepDone} for Set {$finishedSet->id}\n";
            $this->busy = false;
            $this->currentSet = null;
            return $finishedSet;
        }
        return null;
    }
}



