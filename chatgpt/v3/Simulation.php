<?php


class Simulation
{
    private $currentTime = 0;
    private $eventQueue;
    private $seqCounter = 0;

    // Resource busy flags
    private $resources = [
        'soapWash' => false,
        'spinDry' => false,
        'waterWash' => false,
        'hangDry' => false,
    ];
    private $transitionBusy = false;

    // Pending queues of set IDs waiting for each step
    private $pending = [
        'soapWash' => [],
        'spinDry' => [],
        'waterWash' => [],
        'hangDry' => [],
    ];

    private $allowSoapNext = false;
    private $nextSetId = 1;
    private $finite = true;
    private $setsToRun = 0;
    private $limit = null;

    private $completed = 0;
    private $timeline = [];

    public function __construct()
    {
        // Use a max‑heap with composite priorities [-time, -seq]
        $this->eventQueue = new SplPriorityQueue;
    }

    private function scheduleEvent(int $time, callable $cb)
    {
        // priority = [-time, -seq] so that smallest time (largest -time) pops first,
        // and within same time, smaller seq (larger -seq) pops first.
        $this->seqCounter++;
        $priority = [-$time, -$this->seqCounter];
        $this->eventQueue->insert(
            ['time' => $time, 'cb' => $cb],
            $priority
        );
    }

    private function dispatch()
    {
        // If a transition is ongoing, no new start may be scheduled
        if($this->transitionBusy){
            return;
        }
        // Hang Dry has priority over Spin Dry
        if(!$this->resources['hangDry'] && count($this->pending['hangDry']) > 0){
            $setId = array_shift($this->pending['hangDry']);
            $this->scheduleTransition($setId, 'Hang Dry', 5, 'hangDry');
            return;
        }
        if(!$this->resources['spinDry'] && count($this->pending['spinDry']) > 0){
            $setId = array_shift($this->pending['spinDry']);
            $this->scheduleTransition($setId, 'Spin Dry', 3, 'spinDry');
            return;
        }
        if(!$this->resources['waterWash'] && count($this->pending['waterWash']) > 0){
            $setId = array_shift($this->pending['waterWash']);
            $this->scheduleTransition($setId, 'Water Wash', 10, 'waterWash');
            return;
        }
        // Soap Wash: only if allowed by rule (or it's the very first)
        if(!$this->resources['soapWash']
            && ($this->allowSoapNext || $this->currentTime === 0)
            && (
                // either finite with sets waiting
                ($this->finite && count($this->pending['soapWash']) > 0)
                // or unlimited (time‑based run)
                || (!$this->finite)
            )
            && ($this->limit === null || $this->currentTime <= $this->limit)
        ){
            if($this->finite){
                $setId = array_shift($this->pending['soapWash']);
            }
            else{
                $setId = $this->nextSetId++;
            }
            $this->allowSoapNext = false;
            $this->scheduleTransition($setId, 'Soap Wash', 15, 'soapWash');
            return;
        }
    }

    private function scheduleTransition(int $setId, string $stepName, int $duration, string $resName)
    {
        $t0 = $this->currentTime;
        // Transition start at t0
        $this->scheduleEvent($t0, function(){
            $this->transitionBusy = true;
        });
        // Transition ends and step starts at t0+1
        $this->scheduleEvent($t0 + 1, function() use ($setId, $stepName, $duration, $resName, $t0){
            $this->transitionBusy = false;
            $this->startStep($setId, $stepName, $duration, $resName, $t0 + 1);
        });
    }

    private function startStep(int $setId, string $stepName, int $duration, string $resName, int $startTime)
    {
        // Mark resource busy
        $this->resources[$resName] = true;
        // Rule f: once Spin Dry or Hang Dry is *about to start*, allow next Soap
        if(in_array($stepName, ['Spin Dry', 'Hang Dry'])){
            $this->allowSoapNext = true;
        }
        // Log it
        $this->timeline[] = [
            'set' => $setId,
            'step' => $stepName,
            'start' => $startTime,
            'end' => $startTime + $duration
        ];
        // Schedule step completion
        $this->scheduleEvent($startTime + $duration, function() use ($setId, $stepName, $resName){
            // Free resource
            $this->resources[$resName] = false;
            $this->onStepEnd($setId, $stepName);
            $this->dispatch();
        });
    }

    private function onStepEnd(int $setId, string $stepName)
    {
        // Map next step
        switch($stepName){
            case 'Soap Wash':
                $this->pending['spinDry'][] = $setId;
                break;
            case 'Spin Dry':
                $this->pending['waterWash'][] = $setId;
                break;
            case 'Water Wash':
                $this->pending['hangDry'][] = $setId;
                break;
            case 'Hang Dry':
                $this->completed++;
                break;
        }
    }

    public function runForSets(int $n)
    {
        // Finite mode
        $this->finite = true;
        $this->setsToRun = $n;
        $this->limit = null;
        // initialize pending soap for sets 1..n
        for($i = 1; $i <= $n; $i++){
            $this->pending['soapWash'][] = $i;
        }
        // allow first soap wash immediately
        $this->allowSoapNext = true;

        // kick‐off dispatch
        $this->dispatch();

        // process events until all sets complete
        while(!$this->eventQueue->isEmpty() && $this->completed < $n){
            $evt = $this->eventQueue->extract();
            $this->currentTime = $evt['time'];
            $evt['cb']();
        }

        $this->printTimeline();
    }

    public function runForTime(int $minutes)
    {
        // Unlimited mode, time‑limited
        $this->finite = false;
        $this->limit = $minutes;
        $this->allowSoapNext = true;

        // kick‐off initial soap
        $this->dispatch();

        // process events until no more can start and queue empties
        while(!$this->eventQueue->isEmpty()){
            $evt = $this->eventQueue->extract();
            $this->currentTime = $evt['time'];
            if($this->limit !== null && $this->currentTime > $this->limit){
                // don't process any events beyond the time window
                break;
            }
            $evt['cb']();
        }

        echo "Completed {$this->completed} set(s) in {$minutes} minute(s).\n";
    }

    private function printTimeline()
    {
        // sort by start time
        usort($this->timeline, function($a, $b){
            return $a['start'] <=> $b['start'];
        });
        foreach($this->timeline as $e){
            printf(
                "Set %2d | %-10s | start: %3d, end: %3d\n",
                $e['set'],
                $e['step'],
                $e['start'],
                $e['end']
            );
        }
    }
}


