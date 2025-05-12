<?php

require 'Step.php';
require 'Task.php';
require 'Scheduler.php';
// --- Example Usage ---

$scheduler = new Scheduler();
// 2) How many sets in 200 minutes?
$maxSets = $scheduler->setsInTime(200);
echo "\nMaximum full sets in 200 minutes: $maxSets\n";