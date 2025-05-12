<?php

class Simulator
{
    private $steps;
    private $numSets;
    private $maxTime;                  // null or integer
    private $sets = [];
    private $records = [];             // flat log of [setId, stepName, start, end]
    private $soapBusyUntil = 0;
    private $waterBusyUntil = 0;
    private $dryerBusyUntil = 0;
    private $transitionBusyUntil = 0;
    private $allowedSoapStart = [];   // [setId] => time
    private $completedCount = 0;
    private $currentTime = -1;

    public function __construct()
    {
        $this->steps = [
            new ProcessStep('Soap Wash', 15),
            new ProcessStep('Spin Dry', 3),
            new ProcessStep('Water Wash', 10),
            new ProcessStep('Hang Dry', 5),
        ];
    }

    public function simulate(int $numSets, ?int $maxTime = null)
    {
        $this->numSets = $numSets;
        $this->maxTime = $maxTime;
        // init sets and soap‑start rules
        for($i = 1; $i <= $numSets; $i++){
            $this->sets[$i] = new SetJob($i);
            // first set can start soap at time 0; others wait for predecessor’s spin/hang
            $this->allowedSoapStart[$i] = ($i === 1 ? 0 : PHP_INT_MAX);
        }

        // main loop: minute by minute
        for($t = 0; ; $t++){
            // stop if we've run out of time
            if($this->maxTime !== null && $t > $this->maxTime){
                break;
            }
            // stop early if all done and no time limit
            if($this->maxTime === null && $this->completedCount >= $this->numSets){
                break;
            }

            // 1) detect any step completions at t → free resources implicitly by busy‑until checks
            $anyFinished = false;
            foreach($this->sets as $set){
                foreach($set->stepTimings as $idx => $timing){
                    if($timing['end'] === $t){
                        $anyFinished = true;
                    }
                }
                // mark completion
                if($set->stepIndex === count($this->steps) && $set->completionTime === null){
                    $set->completionTime = $t;
                    $this->completedCount++;
                }
            }
            if($anyFinished){
                // one‑minute global transition
                $this->transitionBusyUntil = max($this->transitionBusyUntil, $t + 1);
            }

            // 2) if transition is over, try to start new steps
            if($t >= $this->transitionBusyUntil){
                // a) Hang Dry (highest priority on dryer)
                if($this->dryerBusyUntil <= $t){
                    foreach($this->sets as $set){
                        if($set->stepIndex === 3
                            && isset($set->stepTimings[2])
                            && $set->stepTimings[2]['end'] <= $t
                        ){
                            $this->startStep($set, 3, $t);
                            // Soap‑Wash for next set may start now too
                            $this->allowSoapFor($set->id + 1, $t);
                            break;
                        }
                    }
                }
                // b) Spin Dry
                if($this->dryerBusyUntil <= $t){
                    foreach($this->sets as $set){
                        if($set->stepIndex === 1
                            && isset($set->stepTimings[0])
                            && $set->stepTimings[0]['end'] <= $t
                        ){
                            $this->startStep($set, 1, $t);
                            // now next set can start soap
                            $this->allowSoapFor($set->id + 1, $t);
                            break;
                        }
                    }
                }
                // c) Soap Wash
                if($this->soapBusyUntil <= $t){
                    foreach($this->sets as $set){
                        if($set->stepIndex === 0
                            && $this->allowedSoapStart[$set->id] <= $t
                        ){
                            $this->startStep($set, 0, $t);
                            break;
                        }
                    }
                }
                // d) Water Wash
                if($this->waterBusyUntil <= $t){
                    foreach($this->sets as $set){
                        if($set->stepIndex === 2
                            && isset($set->stepTimings[1])
                            && $set->stepTimings[1]['end'] <= $t
                        ){
                            $this->startStep($set, 2, $t);
                            break;
                        }
                    }
                }
            }
        }

        // output
        if($this->maxTime === null){
            $this->printTimeline();
        }
        else{
            echo "By minute {$this->maxTime}, completed sets: {$this->completedCount}\n";
        }
    }

    private function startStep(SetJob $set, int $stepIdx, int $t)
    {
        $step = $this->steps[$stepIdx];
        // record timings
        $set->stepTimings[$stepIdx] = [
            'start' => $t,
            'end' => $t + $step->duration,
        ];
        $this->records[] = [
            'set' => $set->id,
            'step' => $step->name,
            'start' => $t,
            'end' => $t + $step->duration,
        ];
        // occupy the right resource
        switch($stepIdx){
            case 0:
                $this->soapBusyUntil = $t + $step->duration;
                break;
            case 1:                       // spin:
            case 3:
                $this->dryerBusyUntil = $t + $step->duration;
                break;
            case 2:
                $this->waterBusyUntil = $t + $step->duration;
                break;
        }
        // advance set’s step pointer
        $set->stepIndex++;
    }

    private function allowSoapFor(int $setId, int $time)
    {
        if(isset($this->allowedSoapStart[$setId])){
            // only allow earlier
            $this->allowedSoapStart[$setId] = min($this->allowedSoapStart[$setId], $time);
        }
    }

    private function printTimeline()
    {
        // sort by start time then set
        usort($this->records, function($a, $b){
            if($a['start'] === $b['start']){
                return $a['set'] <=> $b['set'];
            }
            return $a['start'] <=> $b['start'];
        });
        foreach($this->records as $r){
            if($this->currentTime !== $r['start']){
                printf(
                    "%3d | Set %d | %-10s | end: %3d\n",
                    $r['start'],$r['set'], $r['step'],  $r['end']
                );
                $this->currentTime = $r['start'];
            }
            else{
                printf(
                    "    | Set %d | %-10s | end: %3d\n",
                    $r['set'], $r['step'],  $r['end']
                );
            }
        }
        echo "\nAll {$this->numSets} sets complete by minute {$this->sets[$this->numSets]->completionTime}.\n";
    }
}
