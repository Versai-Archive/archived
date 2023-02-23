<?php


namespace Versai\Sumo\Event;


use pocketmine\event\Event;
use Versai\Sumo\Session\Session;

/**
 * @todo
 * Class SumoStartEvent
 * @package Versai\Sumo\Event
 */
class SumoStartEvent extends Event
{
    public Session $session;

    public function __construct(Session $session) {
        $this->session = $session;
    }
}