<?php

namespace ethaniccc\BotDuels\tasks;

use ethaniccc\BotDuels\Utils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class RemoveLevelTask extends AsyncTask {

	private $path;

	public function __construct(string $path) {
		$this->path = $path;
	}

	public function onRun() {
		Utils::rmdirRecursive($this->path);
	}

	public function onCompletion(Server $server) {
		$server->getLogger()->debug("World {$this->path} was removed");
	}

}