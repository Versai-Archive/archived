<?php
declare(strict_types=1);

namespace Versai\Duels\Match;

use pocketmine\player\Player;

class FightEndState {

    /** @var Player $winner */
    private Player $winner;
    /** @var Player $loser */
    private Player $loser;

    /** @var float $winnerHealth */
    private float $winnerHealth;

    /**
     * FightEndState constructor.
     * @param Player $winner
     * @param Player $loser
     */
    public function __construct(Player $winner, Player $loser) {
        $this->winner = $winner;
        $this->loser = $loser;
        $this->winnerHealth = $winner->getHealth();
    }

    /**
     * @return Player
     */
    public function getWinner(): Player {
        return $this->winner;
    }

    /**
     * @return Player
     */
    public function getLoser(): Player {
        return $this->loser;
    }

    /**
     * Not passed by reference like the winning and losing players
     * @return float
     */
    public function getWinnerHealth(): float {
        return $this->winnerHealth;
    }

}