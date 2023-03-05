<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/11/2019
 * Time: 7:12 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Match;

use pocketmine\item\Item;
use pocketmine\player\Player;

class FightEndState
{
    /** @var Player $winner */
    private $winner;
    /** @var Player $loser */
    private $loser;

    /** @var Item[] */
    private $winnerInventory;
    /** @var Item[] */
    private $loserInventory;

    /** @var Item[] $winnerArmor */
    private $winnerArmor;
    /** @var Item[] $loserArmor */
    private $loserArmor;

    /** @var float $winnerHealth */
    private $winnerHealth;

    /**
     * FightEndState constructor.
     * @param Player $winner
     * @param Player $loser
     */
    public function __construct(Player $winner, Player $loser) {
        $this->winner = $winner;
        $this->loser = $loser;
        $this->winnerInventory = $winner->getInventory()->getContents(true);
        $this->winnerArmor = $winner->getArmorInventory()->getContents(true);
        $this->loserInventory = $loser->getInventory()->getContents(true);
        $this->loserArmor = $loser->getArmorInventory()->getContents(true);
        $this->winnerHealth = $winner->getHealth();
    }

    /**
     * @return array
     */
    public function getWinnerInventoryContents(): array{
        return $this->winnerInventory;
    }

    /**
     * @return array
     */
    public function getLoserInventoryContents(): array{
        return $this->loserInventory;
    }

    /**
     * @return array
     */
    public function getWinnerArmor(): array{
        return $this->winnerArmor;
    }

    /**
     * @return array
     */
    public function getLoserArmor(): array{
        return $this->loserArmor;
    }

    /**
     * @return Player
     */
    public function getWinner(): Player{
        return $this->winner;
    }

    /**
     * @return Player
     */
    public function getLoser(): Player{
        return $this->loser;
    }

    /**
     * Not passed by reference like the winning and losing players
     * @return float
     */
    public function getWinnerHealth(): float{
        return $this->winnerHealth;
    }

}