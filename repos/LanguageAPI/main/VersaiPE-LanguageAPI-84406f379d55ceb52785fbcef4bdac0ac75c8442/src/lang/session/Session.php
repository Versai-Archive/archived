<?php


namespace lang\session;


use pocketmine\player\Player;

class Session
{

    private $player;

    private $currentLang = null;

    public function __construct(Player $player)
    {

        $this->player = $player;

    }

    /**
     * @return null
     */
    public function getCurrentLang()
    {
        return $this->currentLang;
    }

    /**
     * @param null $currentLang
     */
    public function setCurrentLang($currentLang): void
    {
        $this->currentLang = $currentLang;
    }



}