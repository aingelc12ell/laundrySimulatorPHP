<?php
class Simulator
{
    private $soapMachine;
    private $spinMachine;
    private $waterMachine;
    private $hangMachine;
    private $transitionRemaining = 0;
    private $time = 0;
    private $sets = [];
    private $nextSetId = 1;
    private $completedCount = 0;
    private $setsToStart;

    public function __construct() {
        $this->soapMachine = new Machine(15);
        $this->spinMachine = new Machine(3);
        $this->waterMachine = new Machine(10);
        $this->hangMachine = new Machine(5);
        $this->setsToStart = PHP_INT_MAX;
    }

    // Simulate until given number of sets complete, return minutes used
    public function simulateForSets(int $numSets): int {
        $this->reset();
        $this->setsToStart = $numSets;
        while ($this->completedCount < $numSets) {
            $this->tick();
        }
        return $this->time;
    }

    // Simulate for given minutes, return sets completed
    public function simulateForTime(int $minutes): int {
        $this->reset();
        while ($this->time < $minutes) {
            $this->tick();
        }
        return $this->completedCount;
    }

    private function reset() {
        $this->transitionRemaining = 0;
        $this->time = 0;
        $this->sets = [];
        $this->nextSetId = 1;
        $this->completedCount = 0;
        $this->setsToStart = PHP_INT_MAX;
        $this->soapMachine = new Machine(15);
        $this->spinMachine = new Machine(3);
        $this->waterMachine = new Machine(10);
        $this->hangMachine = new Machine(5);
    }

    private function tick() {
        // Advance time
        $this->time++;

        // Tick machines and collect finished sets
        $finished = [];
        foreach ([$this->soapMachine, $this->spinMachine, $this->waterMachine, $this->hangMachine] as $machine) {
            $set = $machine->tick();
            if ($set) {
                $finished[] = $set;
            }
        }

        // Process finishes and start transition
        foreach ($finished as $set) {
            $set->simulatorTime = $this->time;
            if ($set->state < 4) {
                $set->state++;
                $this->transitionRemaining = 1;
            } else {
                // Completed hang dry
                $this->completedCount++;
                echo "Minute {$this->time}: Set {$set->id} fully completed (all steps)\n";
            }
        }

        // Decrement transition timer
        if ($this->transitionRemaining > 0) {
            $this->transitionRemaining--;
            return;
        }

        // Try to start steps in order of priority
        // Hang Dry
        foreach ($this->sets as $set) {
            if ($set->state === 3 && !$this->hangMachine->isBusy()) {
                $this->hangMachine->assign($set, $this->time, 'Hang Dry');
                break;
            }
        }
        // Spin Dry
        foreach ($this->sets as $set) {
            if ($set->state === 1 && !$this->spinMachine->isBusy() && !$this->hangMachine->isBusy()) {
                $this->spinMachine->assign($set, $this->time, 'Spin Dry');
                break;
            }
        }
        // Soap Wash for next set
        if (!$this->soapMachine->isBusy() && $this->transitionRemaining === 0 && $this->nextSetId <= $this->setsToStart) {
            $newSet = new Set($this->nextSetId++);
            $this->sets[] = $newSet;
            $this->soapMachine->assign($newSet, $this->time, 'Soap Wash');
        }
        // Water Wash
        foreach ($this->sets as $set) {
            if ($set->state === 2 && !$this->waterMachine->isBusy()) {
                $this->waterMachine->assign($set, $this->time, 'Water Wash');
                break;
            }
        }
    }
}