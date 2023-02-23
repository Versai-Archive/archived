<?php


namespace Versai\Sumo\Session;


use pocketmine\level\Level;
use pocketmine\math\Vector3;

class CachedArena
{
    public Level $level;

    public string $name;

    public Vector3 $joiningPosition;

    public Vector3 $playingPosition1;

    public Vector3 $playingPosition2;


    public function __construct(Level $level, string $name, Vector3 $joiningPosition, Vector3 $playingPosition1, Vector3 $playingPosition2)
    {
        $this->level = $level;
        $this->name = $name;
        $this->joiningPosition = $joiningPosition;
        $this->playingPosition1 = $playingPosition1;
        $this->playingPosition2 = $playingPosition2;
    }
}