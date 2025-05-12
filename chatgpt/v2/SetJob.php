<?php

class SetJob
{
    public $id;
    public $stepIndex = 0;             // next step to do: 0..3
    public $stepTimings = [];          // [stepIndex] => ['start'=>t, 'end'=>t]
    public $completionTime = null;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}