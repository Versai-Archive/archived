<?php

namespace ethaniccc\BotDuels\tasks;

use ethaniccc\BotDuels\Utils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class RemoveLevelTask extends AsyncTask {

	private string $path;

	public function __construct(string $path) {
		$this->path = $path;
	}

	public function onRun(): void {
		Utils::rmdirRecursive($this->path);
	}

	public function onCompletion(): void {
	    $server = Server::getInstance();
		$server->getLogger()->debug("World {$this->path} was removed");
	}

}