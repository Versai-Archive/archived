<?php


namespace Martin\GameAPI\Event;


use Martin\GameAPI\Game\Game;
use Martin\GameAPI\Game\Team\Team;
use Martin\GameAPI\GamePlugin;

class GameStartEvent extends BaseGameEvent
{
    private Team $winner;

    public function __construct(GamePlugin $plugin, Game $game, Team $winner)
    {
        $this->winner = $winner;
        parent::__construct($plugin, $game);
    }

    protected function handleEvent(): void
    {
        $this->getGame()->endGame($this->winner);
    }
}