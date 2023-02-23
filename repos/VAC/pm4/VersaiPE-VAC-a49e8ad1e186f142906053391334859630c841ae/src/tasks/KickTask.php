<?php

namespace ethaniccc\VAC\tasks;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;

final class KickTask extends Task {

	public function __construct(
		public Player $player,
		public string $message
	) {
	}

	public function onRun(): void {
		$this->player->kick($this->message, false);
	}

}