<?php

declare(strict_types=1);

namespace Versai\OneBlock\Events;

use pocketmine\event\Event;
use pocketmine\player\Player;
use Versai\OneBlock\OneBlock\OneBlock;

class IslandCreateEvent extends Event {

	public function __construct(OneBlock $island, Player $player) {
		$this->player = $player;
		$this->island = $island;
	}

	public function getIsland(): OneBlock {
		return $this->island;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

}