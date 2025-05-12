<?php

class Set
{
    public $id;
    public $state = 0; // 0:not started,1:soap done,2:spin done,3:water done,4:hang done
    public $simulatorTime = 0;

    private static $stepNames = [
        0 => 'Not started',
        1 => 'Soap Wash',
        2 => 'Spin Dry',
        3 => 'Water Wash',
        4 => 'Hang Dry'
    ];

    public function __construct(int $id) {
        $this->id = $id;
    }

    public function getStepName(): string {
        return self::$stepNames[$this->state] ?? 'Unknown';
    }
}