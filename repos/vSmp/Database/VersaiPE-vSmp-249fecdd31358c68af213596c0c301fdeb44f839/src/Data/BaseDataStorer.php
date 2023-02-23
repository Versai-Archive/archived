<?php

declare(strict_types=1);

namespace Versai\RPGCore\Data;

use Versai\RPGCore\Main;
use pocketmine\player\Player;
use Versai\RPGCore\Sessions\PlayerSession;

abstract class BaseDataStorer {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    protected function getPlugin(): Main {
        return $this->plugin;
    }

    public abstract function initTables(): void;

    public abstract function registerPlayer(Player $player): void;

    public abstract function getAllPlayerData(Player $player): void;

    public abstract function getClass(Player $player): void;

    public abstract function getMaxMana(Player $player): void;

    public abstract function getDefense(Player $player): void;

    public abstract function getAgility(Player $player): void;

    public abstract function getCoins(Player $player): void;

    public abstract function getQuest(Player $player): void;

    public abstract function setPlayerData(PlayerSession $session): void;
}