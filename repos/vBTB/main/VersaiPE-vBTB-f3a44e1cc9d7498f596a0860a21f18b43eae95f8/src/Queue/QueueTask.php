<?php

declare(strict_types=1);

namespace Versai\BTB\Queue;

use pocketmine\scheduler\Task;
use Versai\BTB\BTB;

class QueueTask extends Task {

	private BTB $plugin;

	public function __construct(BTB $plugin) {
		$this->plugin = $plugin;
	}

	public function onRun(): void {
		$manager = $this->plugin->getQueueManager();

		if ($manager->checkForMatches()) {
			$manager->startMatch();
		}
	}

}