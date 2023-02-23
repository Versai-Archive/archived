<?php

namespace Versai\vStaff\tasks;

use pocketmine\scheduler\Task;

class DelayedHotBar extends Task {

	private $player;
	private $callback;
	private $hud;

	public function __construct($player, $hud, $callback) {
		$this->player = $player;
		$this->callback = $callback;
		$this->hud = $hud;
	}

	public function onRun(): void {
		$yikes = $this->callback;
		$yikes($this->player, $this->hud);
	}
}