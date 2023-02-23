<?php

namespace ethaniccc\VAC\tasks;

use ethaniccc\VAC\VAC;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class KickTask extends Task {

	public function __construct(
		public Player $player,
		public string $message
	) {}

	public function onRun(int $currentTick) {
		$this->player->kick($this->message, false);
	}

}