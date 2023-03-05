<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Events;

use pocketmine\event\Event;
use Versai\OneBlock\OneBlock\OneBlock;

class IslandLevelUpEvent extends Event {

	public function __construct(OneBlock $island) {
		$this->island = $island;
	}

	public function getIsland(): OneBlock {
		return $this->island;
	}

}