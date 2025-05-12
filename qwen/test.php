<?php

require 'Simulator.php';

$ARGUMENTS = $ARGUMENTS ?? [];
if(count($argv)){
    foreach($argv as $aa){
        if(str_contains($aa, "=")){
            [$kk, $vv] = explode("=", $aa);
            $ARGUMENTS[$kk] = $vv;
        }
        else{
            $ARGUMENTS[] = $aa;
        }
    }
}

// Example Usage
$simulator = new Simulator();

// Calculate time for N sets
$numSets = $ARGUMENTS['sets'] ?? 5;
echo "🚀 Starting simulation for $numSets sets...\n";
$timeNeeded = $simulator->simulateSets($numSets);
echo "🏁 Simulation completed in $timeNeeded minutes\n\n";

/*// Calculate maximum sets in given time
$maxTime = 60;
echo "📊 Calculating maximum sets that can be completed in $maxTime minutes...\n";
$maxSets = $simulator->findMaxSets($maxTime);
echo "✅ Maximum sets completed in $maxTime minutes: $maxSets sets\n";*/