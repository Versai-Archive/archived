<?php


namespace Versai\Sumo\Event;


use pocketmine\event\Event;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use Versai\Sumo\Session\Session;
use Versai\Sumo\Sumo;

/**
 * @todo
 * Class SumoPreStartEvent
 * @package Versai\Sumo\Event
 * @description Player created a sumo tournament
 */
class SumoPreStartEvent extends Event
{
    public function __construct(Player $owner, Sumo $sumo, Level $map, Vector3 $joiningPosition, Vector3 $playingPosition1, Vector3 $playingPosition2) {
        new Session($sumo, $map, $joiningPosition, $playingPosition1, $playingPosition2);
    }
}