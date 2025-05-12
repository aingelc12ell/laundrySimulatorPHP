<?php


class Scheduler
{
    protected $steps;
    protected $transitionTime = 1;

    public function __construct()
    {
        // define the four steps
        $this->steps = [
            new Step('Soap Wash', 15, false, null, 1),
            new Step('Spin Dry', 3, false, 'spin_hang', 1),
            new Step('Water Wash', 10, true, null, 0),
            new Step('Hang Dry', 5, false, 'spin_hang', 2),
        ];
    }

    /**
     * Simulate N sets and returns an array of Task objects (timeline).
     */
    public function simulateSets(int $numSets): array
    {
        $timeline = [];
        $currentTime = 0;
        $nextSoapStart = 0;

        for($set = 1; $set <= $numSets; $set++){
            // For each step in order:
            foreach($this->steps as $step){
                // ensure step can't start before end of any exclusive-step running with higher priority
                $earliest = $currentTime;

                // enforce that Soap Wash of next set doesn't start too early
                if($step->name === 'Soap Wash'){
                    $earliest = max($earliest, $nextSoapStart);
                }

                // find if any conflicting tasks in timeline block this
                foreach($timeline as $task){
                    // if transition period exclusive
                    $latestTransitionStart = $earliest - $this->transitionTime;
                    if(!$step->canRunDuringTransition){
                        // can't overlap transition from any prior task
                        if($earliest < $task->end + $this->transitionTime && $earliest >= $task->end){
                            $earliest = $task->end + $this->transitionTime;
                        }
                    }
                    // group mutual exclusion
                    if($step->exclusiveGroup
                        && $task->step->exclusiveGroup === $step->exclusiveGroup){
                        // enforce serialization by priority
                        if($task->end > $earliest){
                            if($task->step->priority >= $step->priority){
                                $earliest = $task->end + $this->transitionTime;
                            }
                        }
                    }
                }

                // create the task
                $task = new Task($set, $step, $earliest);
                $timeline[] = $task;

                // update global current time
                $currentTime = max($currentTime, $task->end);

                // If this is Spin Dry or Hang Dry, schedule the next Soap start
                if(in_array($step->name, ['Spin Dry', 'Hang Dry'])){
                    // Soap Wash of next set can only start when one of these is about to start
                    $nextSoapStart = $earliest;
                }
            }
        }

        // sort timeline by start time
        usort($timeline, function($a, $b){
            return $a->start <=> $b->start;
        });

        return $timeline;
    }

    /**
     * Given a total time budget, how many full sets complete?
     */
    public function setsInTime(int $minutes): int
    {
        // simulate until we exceed minutes
        $count = 0;
        $time = 0;
        $nextSoap = 0;

        while(true){
            // simulate one more set quickly by just summing the critical path:
            // Soap + transition + Spin Dry + transition + Hang Dry + transition
            // Water Wash can overlap in parallel up to transition constraints,
            // but the critical path is Soap(15)->T->Hang(5)->T->Spin(3) + T's:
            $critical = 15 + $this->transitionTime
                + 5 + $this->transitionTime
                + 3 + $this->transitionTime;
            $time += $critical;
            if($time > $minutes){
                break;
            }
            ++$count;
        }

        return $count;
    }
}