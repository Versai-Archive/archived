<?php


namespace session;


use pocketmine\player\Player;

class Session
{

    private $player;

    private $currentMatch = null;

    private $deviceOs = null;

    private int $streakKills = 0;

    private int $streakWins = 0;

    public function __construct(Player $player)
    {

        $this->player = $player;

    }

    /**
     * @return null
     */
    public function getCurrentMatch()
    {
        return $this->currentMatch;
    }

}