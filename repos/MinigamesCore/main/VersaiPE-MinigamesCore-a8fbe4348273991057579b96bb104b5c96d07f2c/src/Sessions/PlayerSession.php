<?php

declare(strict_types=1);

namespace Versai\Sessions;

use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\player\Player;

class PlayerSession {

	private Player $player;
	public $input;

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function getInputType() {
		return $this->input;
	}

	public function getInputString(): string {
		return match($this->input) {
			InputMode::MOUSE_KEYBOARD => "KBM",
			InputMode::TOUCHSCREEN => "TOUCH",
			InputMode::GAME_PAD => "GAMEPAD",
			InputMode::MOTION_CONTROLLER => "MOTION CONTROLLER",
			default => "CANT FIND"
		};
	}

	public function setInput(int $input) {
		$this->input = $input;
	}
}