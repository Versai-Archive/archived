<?php


namespace Versai\Sumo\Event;


use pocketmine\Player;
use Versai\Sumo\Session\Session;

/**
 * @todo
 * Class SumoEndEvent
 * @package Versai\Sumo\Event
 */
class SumoEndEvent
{
    public Session $session;

    private Player $winner;

    public function __construct(Session $session, Player $winner) {
        $this->session = $session;
        $this->winner = $winner;
    }
}