<?php

include(__DIR__ . '/Machine.php');
include(__DIR__ . '/Set.php');
include(__DIR__ . '/Simulator.php');

// Example Usage:
$sim = new Simulator();
echo "Time for 2 sets: " . $sim->simulateForSets(2) . " minutes\n";
# echo "Sets completed in 60 minutes: " . $sim->simulateForTime(60) . " sets\n";
