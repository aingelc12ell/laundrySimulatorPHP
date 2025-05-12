<?php

// laundry_sim.php

class ProcessStep
{
    public $name;
    public $duration;

    public function __construct(string $name, int $duration)
    {
        $this->name = $name;
        $this->duration = $duration;
    }
}




