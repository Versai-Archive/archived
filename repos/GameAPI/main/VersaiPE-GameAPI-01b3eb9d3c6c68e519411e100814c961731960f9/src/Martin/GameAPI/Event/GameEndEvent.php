<?php


namespace Martin\GameAPI\Event;


use Martin\GameAPI\Game\Game;
use Martin\GameAPI\GamePlugin;

class GameEndEvent extends BaseGameEvent
{
    public function __construct(GamePlugin $plugin, Game $game)
    {
        parent::__construct($plugin, $game);
    }

    protected function handleEvent(): void
    {
        $this->getGame()->startGame();
    }
}