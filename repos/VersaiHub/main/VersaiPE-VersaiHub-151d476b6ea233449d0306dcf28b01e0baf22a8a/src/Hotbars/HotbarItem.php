<?php

declare(strict_types=1);

namespace Versai\Hotbars;

use Closure;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class HotbarItem extends Item {

	public function __construct(ItemIdentifier $identifier, Closure $onInteract , string $name = "Unknown") {
		$this->onInteract = $onInteract;
		parent::__construct($identifier, $name);
	}

	public function handleInteraction() {
		$this->onInteract->call($this);
	}

}