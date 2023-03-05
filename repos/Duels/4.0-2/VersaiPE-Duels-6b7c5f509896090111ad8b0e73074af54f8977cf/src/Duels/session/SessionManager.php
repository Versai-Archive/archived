<?php

namespace session;

use pocketmine\player\Player;

class SessionManager
{

    private $sessions = [];


    public function createSession(Player $player):void
    {

        $this->sessions[$player->getUniqueId()->toString()] = new Session($player);

    }

    public function deleteSession(Player $player):void
    {

        unset($this->sessions[$player->getUniqueId()->toString()]);

    }

    public function sessionExist(Player $player):bool
    {

        return isset($this->sessions[$player->getUniqueId()->toString()]);

   }

    public function getSession(Player $player):Session
    {

        if (!$this->sessionExist($player)) {

           $this->createSession($player);

        }

        return $this->sessions[$player->getUniqueId()->toString()];

   }

}