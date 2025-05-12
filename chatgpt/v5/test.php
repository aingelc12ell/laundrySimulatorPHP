<?php


class Step
{
    public $name;
    public $duration;
    public $canOverlap;       // true if can overlap with other steps (water wash)
    public $exclusiveGroup;   // group name for mutual exclusion (spin_hang)
    public $priority;         // higher value = higher priority

    public function __construct(string $name, int $duration, bool $canOverlap = false, string $exclusiveGroup = null, int $priority = 0)
    {
        $this->name = $name;
        $this->duration = $duration;
        $this->canOverlap = $canOverlap;
        $this->exclusiveGroup = $exclusiveGroup;
        $this->priority = $priority;
    }
}

class Task
{
    public $setId;
    public $step;
    public $start;
    public $end;

    public function __construct(int $setId, Step $step, int $start)
    {
        $this->setId = $setId;
        $this->step = $step;
        $this->start = $start;
        $this->end = $start + $step->duration;
    }
}

class Scheduler
{
    protected $steps;
    protected $transition = 1;
    protected $tasks = [];

    public function __construct()
    {
        $this->steps = [
            new Step('Soap Wash', 15, false, null, 1),
            new Step('Spin Dry', 3, false, 'spin_hang', 1),
            new Step('Water Wash', 10, true, null, 0),
            new Step('Hang Dry', 5, false, 'spin_hang', 2),
        ];
    }

    public function simulateSets(int $numSets): array
    {
        $this->tasks = [];
        $eventQueue = [];  // min-heap of [time, type, info]
        $currentTime = 0;

        // start first Soap
        $eventQueue[] = ['time' => 0, 'action' => 'start', 'set' => 1, 'stepIdx' => 0];
        usort($eventQueue, fn($a, $b) => $a['time'] <=> $b['time']);

        while(!empty($eventQueue)){
            $event = array_shift($eventQueue);
            $currentTime = $event['time'];
            $setId = $event['set'];
            $stepIdx = $event['stepIdx'];
            $step = $this->steps[$stepIdx];

            if($event['action'] === 'start'){
                // create task and schedule end
                $task = new Task($setId, $step, $currentTime);
                $this->tasks[] = $task;

                // schedule end event
                $eventQueue[] = [
                    'time' => $task->end,
                    'action' => 'end',
                    'set' => $setId,
                    'stepIdx' => $stepIdx
                ];
                usort($eventQueue, fn($a, $b) => $a['time'] <=> $b['time']);

            }
            else{ // end event
                // after end + transition, queue next step
                $nextIdx = $stepIdx + 1;
                if($nextIdx < count($this->steps)){
                    $readyTime = $currentTime + $this->transition;

                    // find actual earliest time respecting exclusivity and transitions
                    $readyTime = $this->resolveConstraints($readyTime, $this->steps[$nextIdx]);

                    $eventQueue[] = [
                        'time' => $readyTime,
                        'action' => 'start',
                        'set' => $setId,
                        'stepIdx' => $nextIdx
                    ];
                    usort($eventQueue, fn($a, $b) => $a['time'] <=> $b['time']);
                }

                // queue next set's Soap Wash when Spin or Hang starts
                if(in_array($step->name, ['Spin Dry', 'Hang Dry']) && $setId < $numSets){
                    $nextSoapIdx = 0;
                    $startSoap = $this->resolveConstraints($currentTime, $this->steps[$nextSoapIdx]);
                    $eventQueue[] = [
                        'time' => $startSoap,
                        'action' => 'start',
                        'set' => $setId + 1,
                        'stepIdx' => $nextSoapIdx
                    ];
                    usort($eventQueue, fn($a, $b) => $a['time'] <=> $b['time']);
                }
            }
        }

        usort($this->tasks, fn($a, $b) => $a->start <=> $b->start);
        return $this->tasks;
    }

    protected function resolveConstraints(int $time, Step $newStep): int
    {
        $t = $time;
        foreach($this->tasks as $task){
            // no overlap during transition unless canOverlap
            if(!$newStep->canOverlap){
                if($t < $task->end + $this->transition && $t >= $task->end){
                    $t = $task->end + $this->transition;
                }
            }
            // mutual exclusion group
            if($newStep->exclusiveGroup && $task->step->exclusiveGroup === $newStep->exclusiveGroup){
                if($task->end > $t){
                    if($task->step->priority >= $newStep->priority){
                        $t = $task->end + $this->transition;
                    }
                }
            }
        }
        return $t;
    }

    public function printTimeline(array $tasks)
    {
        echo "Timeline (Set, Step, Start→End)\n";
        foreach($tasks as $task){
            echo sprintf("Set%-2d %-10s %3d→%3d\n",
                $task->setId,
                $task->step->name,
                $task->start,
                $task->end
            );
        }
    }
}

// Example
$scheduler = new Scheduler();
$timeline = $scheduler->simulateSets(5);
$scheduler->printTimeline($timeline);
