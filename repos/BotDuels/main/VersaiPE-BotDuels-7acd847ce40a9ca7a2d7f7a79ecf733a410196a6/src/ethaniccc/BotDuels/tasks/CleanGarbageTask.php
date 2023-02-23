<?php

namespace ethaniccc\BotDuels\tasks;

use ethaniccc\BotDuels\Utils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CleanGarbageTask extends AsyncTask {

	public function onRun() {
		$trashed = "";
		Utils::iterate(scandir("./worlds/"), function ($key, string $file) use (&$trashed): void {
			$splitted = explode("-", $file)[1] ?? null;
			if ($splitted !== null) {
				if (strlen($splitted) === 15 && is_dir("./worlds/$file")) {
					Utils::rmdirRecursive("./worlds/$file");
					$trashed .= "./worlds/$file && ";
				}
			}
		});
		$this->setResult(substr($trashed, 0, strlen($trashed) - 4));
	}

	public function onCompletion(Server $server) {
		$trashed = $this->getResult();
		if ($this->getResult() === false) {
			$server->getLogger()->debug("No trash worlds found");
		} else {
			$server->getLogger()->debug("Trash removed: $trashed");
		}
	}

}