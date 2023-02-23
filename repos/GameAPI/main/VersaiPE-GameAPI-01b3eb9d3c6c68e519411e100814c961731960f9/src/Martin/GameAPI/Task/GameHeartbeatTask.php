<?php


namespace Martin\GameAPI\Task;


use Martin\GameAPI\Game\Game;
use pocketmine\scheduler\Task;

/**
 * Class GameHeartbeatTask
 * @package Martin\GameAPI\Task
 * @todo Even todo?
 */
class GameHeartbeatTask extends Task
{
    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function onRun(int $currentTick): void
    {

    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }
}