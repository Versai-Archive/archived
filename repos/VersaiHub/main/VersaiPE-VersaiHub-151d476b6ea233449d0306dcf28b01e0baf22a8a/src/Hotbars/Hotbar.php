<?php

declare(strict_types=1);

namespace Versai\Hotbars;

use pocketmine\player\Player;

class Hotbar {

	private array $items;

	private string $name;

	public function __construct(string $name, array $items = []) {
		$this->name = $name;
		$this->items = $items;
	}

	public function setItem(HotbarItem $item, int $slot = 0) {
		$this->items[$slot] = $item;
	}

	public function getItems(): array {
		return $this->items;
	}

	public function getName(): string {
		return $this->name;
	}

	public function sendTo(Player $player) {
		foreach ($this->items as $slot => $item) {
			$player->getInventory()->setItem($slot, $item);
		}
	}

}