<?php

namespace ethaniccc\VAC\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

final class FetchLogTask extends AsyncTask {

	public function __construct(
		public string $target,
		callable $onComplete
	) {
		$this->storeLocal($onComplete);
	}

	public function onRun() {
		if (!file_exists("plugin_data/VAC/logs/{$this->target}")) {
			$this->setResult(false);
			return;
		}

		$decoded = json_decode(file_get_contents("plugin_data/VAC/logs/{$this->target}"), true);
		if ($decoded === false) {
			$this->setResult("ERR: Unable to decode data from cached file");
		} else {
			$message = "";
			foreach ($decoded as $sub) {
				foreach ($sub as $n => $d) {
					if (is_array($d)) {
						$d = str_replace("\n", "", var_export($d, true));
					}
					$message .= "$n: $d ";
				}
				$message .= PHP_EOL;
			}
			$this->setResult($message);
		}
	}

	public function onCompletion(Server $server) {
		($this->fetchLocal())($this->getResult());
	}

}