<?php

namespace versai\gameapi\arena;

use pocketmine\math\Vector3;

class ArenaPosition extends Vector3
{
    public function __construct(int|float $x, int|float $y, int|float $z, public int|float $yaw, public int|float $pitch)
    {
        parent::__construct($x, $y, $z);
    }
}