<?php

namespace Duels\game;

class GameMatch
{

    private String $name;

    public function __construct($name)
    {



    }

    /**
     * @return String
     */
    public function getName(): string
    {
        return $this->name;
    }

}