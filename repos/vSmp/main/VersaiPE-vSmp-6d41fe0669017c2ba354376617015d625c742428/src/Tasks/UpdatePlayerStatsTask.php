<?php

declare(strict_types=1);

namespace Versai\RPGCore\Tasks;

use pocketmine\scheduler\Task;
use Versai\RPGCore\Main;
use pocketmine\entity\Attribute;

class UpdatePlayerStatsTask extends Task {
    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $session = $this->plugin->getSessionManager();
            $playerSession = $session->getSession($player);

            if(!$playerSession) {
                return;
            }

            if (!$playerSession->getAgility()) {
                return;
            }

            $movement = $player->getAttributeMap()->get(Attribute::MOVEMENT_SPEED);
            $movement->setValue($playerSession->getAgility());
        }
    }
}