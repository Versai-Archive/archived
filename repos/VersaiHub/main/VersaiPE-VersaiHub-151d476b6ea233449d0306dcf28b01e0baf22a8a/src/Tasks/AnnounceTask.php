<?php

declare(strict_types=1);

namespace Versai\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use Versai\Main;

class AnnounceTask extends CustomRepeatingTask {

	private int $iteration = 0;

	public function onRun(): void{
		$config = Main::getInstance()->getConfig();

		$announcements = $config->getNested("Announcements");

		$configAmt = count($announcements);

		if ($configAmt - 1 < $this->iteration) {
			$this->iteration = 0;
		}

		Server::getInstance()->broadcastMessage($announcements[$this->iteration]);
		$this->iteration++;
	}

}