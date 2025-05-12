<?php

require "LaundrySimulator.php";

// Example of usage
$simulator = new LaundrySimulator(3); // Simulate for 3 sets
$totalTime = $simulator->simulate();
echo "Total time for 3 sets: {$totalTime} minutes\n";

$setsInTime = $simulator->setsCompletedInTime(100); // Calculate sets in 100 minutes
echo "Sets completed in 100 minutes: {$setsInTime}\n";