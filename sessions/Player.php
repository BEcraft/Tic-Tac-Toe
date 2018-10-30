<?php

class Player {

    private $name;
    private $colour;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setColour($colour) {
        $this->colour = $colour;
    }

    public function getColour() {
        return $this->colour;
    }

}