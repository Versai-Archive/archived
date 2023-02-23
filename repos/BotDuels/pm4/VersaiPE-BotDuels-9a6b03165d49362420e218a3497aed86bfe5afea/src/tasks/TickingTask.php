<?php

namespace ethaniccc\BotDuels\tasks;

use ethaniccc\BotDuels\game\GameManager;
use pocketmine\scheduler\Task;

class TickingTask extends Task {

	public function onRun(): void {
		GameManager::getInstance()->tick();
	}

}