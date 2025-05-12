<?php

require "Simulation.php";
// ——— Usage Examples ———
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

// 1) Simulate exactly 3 sets:
$sim = new Simulation();
$sets = $ARGUMENTS['sets'] ?? 5;
echo "Timeline for " . $sets . " sets:\n";
$sim->runForSets($sets);

echo "\n";

// 2) How many sets finish in 200 minutes?
# $sim2 = new Simulation();
# $sim2->runForTime(200);