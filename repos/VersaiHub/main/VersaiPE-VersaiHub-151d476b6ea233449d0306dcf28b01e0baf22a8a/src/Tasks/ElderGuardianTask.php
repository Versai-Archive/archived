<?php

namespace Versai\Tasks;

use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\scheduler\Task;
use pocketmine\player\Player;

class ElderGuardianTask extends Task {

    private $player;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(): void {
        $pk = new LevelEventPacket();
        $pk->eventId = LevelEvent::GUARDIAN_CURSE;
        $pk->eventData = 0;
        $pk->position = $this->player->getPosition();
        $this->player->getNetworkSession()->sendDataPacket($pk);
    }

}
