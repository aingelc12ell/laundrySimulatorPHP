<?php

class LaundrySimulator {
    private $sets;
    private $totalTime;

    const SOAP_WASH_TIME = 15;
    const SPIN_DRY_TIME = 3;
    const WATER_WASH_TIME = 10;
    const HANG_DRY_TIME = 5;
    const TRANSITION_TIME = 1;

    public function __construct($sets) {
        $this->sets = $sets;
        $this->totalTime = 0;
    }

    public function simulate() {
        for ($i = 0; $i < $this->sets; $i++) {
            $this->processSet($i + 1);
        }
        return $this->totalTime;
    }

    private function processSet($setNumber) {
        // Soap Wash
        $this->totalTime += self::SOAP_WASH_TIME;

        // Transition to Spin Dry
        $this->totalTime += self::TRANSITION_TIME;

        // Spin Dry
        $this->totalTime += self::SPIN_DRY_TIME;

        // Transition to Hang Dry
        $this->totalTime += self::TRANSITION_TIME;

        // Hang Dry
        $this->totalTime += self::HANG_DRY_TIME;

        // Water Wash can start after Soap Wash
        // It runs in parallel with Spin Dry and Hang Dry
        $this->totalTime += self::WATER_WASH_TIME;
    }

    public function setsCompletedInTime($minutes) {
        $setsCompleted = 0;
        while ($this->totalTime <= $minutes) {
            $setsCompleted++;
            $this->simulate();
        }
        return $setsCompleted;
    }
}

