<?php
declare(strict_types=1);

namespace Versai\Disguise\Events;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;


class DisableButNotDisguisedEvent extends PlayerEvent {
	/**
	 * DisableButNotDisguisedEvent constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player)
	{
		$this->player = $player;
	}
}