<?php


class Step
{
    public $name;
    public $duration;
    public $canRunDuringTransition;
    public $exclusiveGroup; // null or group name for mutual exclusion
    public $priority;       // higher number → higher priority

    public function __construct(string $name, int $duration, bool $canRunDuringTransition = false, string $exclusiveGroup = null, int $priority = 0)
    {
        $this->name = $name;
        $this->duration = $duration;
        $this->canRunDuringTransition = $canRunDuringTransition;
        $this->exclusiveGroup = $exclusiveGroup;
        $this->priority = $priority;
    }
}







