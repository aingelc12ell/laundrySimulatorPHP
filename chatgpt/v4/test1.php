<?php

require 'Step.php';
require 'Task.php';
require 'Scheduler.php';


// --- Example Usage ---

$scheduler = new Scheduler();
// 1) Generate and print the timeline for 5 sets:
$timeline = $scheduler->simulateSets(5);
echo "Timeline (Set, Step, Start, End):\n";
foreach($timeline as $task){
    echo sprintf(
        "Set %d — %-10s — %3d → %3d\n",
        $task->setId,
        $task->step->name,
        $task->start,
        $task->end
    );
}