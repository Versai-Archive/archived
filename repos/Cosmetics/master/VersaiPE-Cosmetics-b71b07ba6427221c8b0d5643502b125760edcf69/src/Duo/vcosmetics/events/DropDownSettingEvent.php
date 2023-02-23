<?php
declare(strict_types=1);

namespace Duo\vcosmetics\events;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class DropDownSettingEvent extends PlayerEvent {

	private int $state;

	public function __construct(Player $player, int $state) {
		$this->player = $player;
		$this->state = $state;
	}

	public function getState(): int{
		return $this->state;
	}

}