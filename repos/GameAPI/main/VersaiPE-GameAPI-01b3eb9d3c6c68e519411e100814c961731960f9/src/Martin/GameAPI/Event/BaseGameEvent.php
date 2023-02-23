<?php


namespace Martin\GameAPI\Event;


use Martin\GameAPI\Game\Game;
use Martin\GameAPI\GamePlugin;
use pocketmine\event\Event;

abstract class BaseGameEvent extends Event
{
    private GamePlugin $plugin;
    private Game $game;

    public function __construct(GamePlugin $plugin, Game $game)
    {
        $this->plugin = $plugin;
        $this->game = $game;
        $this->handleEvent();
    }

    abstract protected function handleEvent(): void;

    /**
     * @return GamePlugin
     */
    public function getPlugin(): GamePlugin
    {
        return $this->plugin;
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }
}