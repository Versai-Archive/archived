<?php

declare(strict_types=1);

namespace Versai\Tasks;

use pocketmine\scheduler\Task;

abstract class CustomRepeatingTask extends Task {

	public int $ticks;

	public function __construct(int $ticks = 20) {
		$this->ticks = $ticks;
	}

}