<?php

namespace lang\session;

use pocketmine\player\Player;

class SessionManager
{

    private $sessions = [];

    public function __construct()
    {


    }

    public function createSession(Player $player)
    {

        $this->sessions[$player->getUniqueId()->toString()] = new Session($player);

    }

    public function destroySession(Player $player)
    {

     unset($this->sessions[$player->getUniqueId()->toString()]);

    }

    /**
     * @param Player $player
     * @return Session
     */

    public function getSession(Player $player)
    {

        if (!isset($this->sessions[$player->getUniqueId()->toString()])) {

            $this->createSession($player);

        }

        return $this->getSession($player);

    }

    /**
     * @return array
     */
    public function getSessions(): array
    {
        return $this->sessions;
    }

}