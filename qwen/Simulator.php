<?php


class Simulator
{
    private $time;
    private $queue;
    private $spinHangAvailableAt;
    private $blockingUntil;
    private $lastCompletionTime;
    private $completedSets;
    private $steps;
    private $totalSets;
    private $soapScheduled;
    private $setProgress;

    public function __construct()
    {
        $this->time = 0;
        $this->queue = new SplPriorityQueue();
        $this->spinHangAvailableAt = 0;
        $this->blockingUntil = 0;
        $this->lastCompletionTime = 0;
        $this->completedSets = 0;
        $this->steps = [
            ['name' => 'Soap Wash', 'duration' => 15, 'resource' => null],
            ['name' => 'Spin Dry', 'duration' => 3, 'resource' => 'spin_hang'],
            ['name' => 'Water Wash', 'duration' => 10, 'resource' => null],
            ['name' => 'Hang Dry', 'duration' => 5, 'resource' => 'spin_hang'],
        ];
        $this->soapScheduled = [];
        $this->setProgress = [];
    }

    public function simulateSets($numSets)
    {
        $this->reset($numSets);

        // Start Set 0's Soap Wash at time 0
        $this->scheduleStep(0, 0, 0);
        $this->soapScheduled[0] = true;

        while(!$this->queue->isEmpty()){
            $event = $this->queue->extract();
            $this->time = $event['time'];

            switch($event['type']){
                case 'complete':
                    $setId = $event['setId'];
                    $stepNum = $event['step'];
                    $step = $this->steps[$stepNum];

                    echo "✅  $this->time  " . str_repeat("\t", $setId + 1) . "Set $setId, Completed: " . $step['name'] . "\n";

                    $this->completedSets++;
                    if($setId === $numSets - 1 && $stepNum === 3){
                        $this->lastCompletionTime = max($this->lastCompletionTime, $this->time);
                    }
                    $this->scheduleTransition($setId, $stepNum);
                    break;

                case 'transition_end':
                    $setId = $event['setId'];
                    $nextStep = $event['step'];
                    echo "➡️ " . $this->time . " " . str_repeat("\t", $setId + 1) . "Set $setId \tTransition ended, proceeding to step $nextStep .\n";

                    if($nextStep < 4){
                        $this->scheduleStep($setId, $nextStep, $this->time);
                    }
                    break;
            }
        }

        return $this->lastCompletionTime;
    }

    private function reset($numSets)
    {
        $this->time = 0;
        $this->queue = new SplPriorityQueue();
        $this->spinHangAvailableAt = 0;
        $this->blockingUntil = 0;
        $this->lastCompletionTime = 0;
        $this->completedSets = 0;
        $this->totalSets = $numSets;
        $this->soapScheduled = [];
        $this->setProgress = array_fill(0, $numSets, 0); // Tracks step progress
    }

    private function scheduleStep($setId, $stepNum, $startTime)
    {
        $step = $this->steps[$stepNum];
        $resource = $step['resource'];
        $effectiveStart = max($startTime, $this->blockingUntil);

        if($resource === 'spin_hang'){
            $effectiveStart = max($effectiveStart, $this->spinHangAvailableAt);
        }

        $finishTime = $effectiveStart + $step['duration'];

        $this->queue->insert([
            'type' => 'complete',
            'time' => $finishTime,
            'setId' => $setId,
            'step' => $stepNum
        ], -$finishTime);

        echo "▶️ $effectiveStart " . str_repeat("\t", $setId + 1) . " Set $setId, " . $step['name'] . ", ends at $finishTime\n";

        if($resource === 'spin_hang'){
            $this->spinHangAvailableAt = $finishTime;
        }

        // Check if this step allows next set to start Soap Wash
        if(($stepNum == 1 || $stepNum == 3) && $setId < $this->totalSets - 1){
            $nextSetId = $setId + 1;
            if(!isset($this->soapScheduled[$nextSetId])){
                $this->soapScheduled[$nextSetId] = true;
                $this->scheduleStep($nextSetId, 0, $effectiveStart);
            }
        }

        $this->setProgress[$setId] = $stepNum + 1;
    }

    private function scheduleTransition($setId, $currentStep)
    {
        $transitionEnd = $this->time + 1;

        if($transitionEnd > $this->blockingUntil){
            $this->blockingUntil = $transitionEnd;
        }

        $nextStep = $currentStep + 1;
        $this->queue->insert([
            'type' => 'transition_end',
            'time' => $transitionEnd,
            'setId' => $setId,
            'step' => $nextStep
        ], -$transitionEnd);

        echo "🔄 " . $this->time . " " . str_repeat("\t", $setId + 1) . " Transition started at ends at $transitionEnd.\n";
    }

    public function findMaxSets($maxTime)
    {
        $low = 0;
        $high = 100;
        $best = 0;

        while($low <= $high){
            $mid = intval(($low + $high) / 2);
            if($mid === 0){
                $time = 0;
            }
            else{
                $time = $this->simulateSets($mid);
            }

            if($time <= $maxTime){
                $best = $mid;
                $low = $mid + 1;
            }
            else{
                $high = $mid - 1;
            }
        }

        return $best;
    }
}
