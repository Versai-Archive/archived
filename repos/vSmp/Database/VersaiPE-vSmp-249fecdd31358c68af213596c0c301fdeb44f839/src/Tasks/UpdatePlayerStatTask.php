<?php

declare(strict_types=1);

namespace Versai\RPGCore\Tasks;

use pocketmine\scheduler\Task;
use Versai\RPGCore\Main;

class UpdatePlayerStats extends Task {
    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $session = $this->plugin->getSessionManager();
            $playerSession = $session->getSession($player);

            $player->setAgility($playerSession->getAgility());
            $player->setMaxMana($playerSession->getMaxMana());
        }
    }
}