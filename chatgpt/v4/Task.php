<?php
class Task
{
    public $setId;
    public $step;
    public $start;
    public $end;

    public function __construct(int $setId, Step $step, int $start)
    {
        $this->setId = $setId;
        $this->step = $step;
        $this->start = $start;
        $this->end = $start + $step->duration;
    }
}