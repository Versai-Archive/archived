<?php
declare(strict_types=1);

namespace Duo\vcosmetics\events;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

abstract class ToggleSettingEvent extends PlayerEvent {

	private bool $state;

	public function __construct(Player $player, bool $state) {
		$this->player = $player;
		$this->state = $state;
	}

	public function getState(): bool{
		return $this->state;
	}
}