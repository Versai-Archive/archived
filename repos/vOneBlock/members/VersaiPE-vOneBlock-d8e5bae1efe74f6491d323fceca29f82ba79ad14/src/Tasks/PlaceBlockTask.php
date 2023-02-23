<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Tasks;

use pocketmine\block\Block;
use pocketmine\block\Sand;
use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;

class PlaceBlockTask extends Task {

	public function __construct(Block $block, Position $position) {
		$this->position = $position;
		$this->block = $block;
	}

	public function onRun(): void {
		$this->position->getWorld()->setBlock($this->position, $this->block, false);
	}

}