<?php

require 'ProcessStep.php';
require 'SetJob.php';
require 'Simulator.php';


// ---- CLI driver ----
if(php_sapi_name() === 'cli'){
    if($argc < 2){
        echo "Usage: php test.php <numSets> [<timeLimit>]\n";
        exit(1);
    }
    $n = (int)$argv[1];
    $t = $argc >= 3 ? (int)$argv[2] : null;
    $sim = new Simulator();
    $sim->simulate($n, $t);
}
