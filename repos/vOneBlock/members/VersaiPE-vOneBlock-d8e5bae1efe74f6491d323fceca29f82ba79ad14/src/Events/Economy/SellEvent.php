<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Events\Economy;


use pocketmine\event\Event;
use pocketmine\player\Player;

class SellEvent extends Event {

	private Player $player;
	private array $items;
	private int $total;

	public function __construct(Player $player, array $items, int $total) {
		$this->player = $player;
		$this->items = $items;
		$this->total = $total;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function getItems(): array {
		return $this->items;
	}

	public function getTotal(): int {
		return $this->total;
	}

}