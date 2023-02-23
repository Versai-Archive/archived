<?php

declare(strict_types=1);

namespace Versai\BTB\Events;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class PlayerEnterQueueEvent extends PlayerEvent {

	protected $player;

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

}