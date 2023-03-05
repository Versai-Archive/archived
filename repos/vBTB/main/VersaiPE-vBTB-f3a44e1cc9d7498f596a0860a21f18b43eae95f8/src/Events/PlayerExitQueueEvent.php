<?php

declare(strict_types=1);

namespace Versai\BTB\Events;

use pocketmine\event\player\PlayerEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\NetworkSessionManager;
use pocketmine\player\Player;
use pocketmine\Server;

class PlayerExitQueueEvent extends PlayerEvent {

	protected $player;

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

}