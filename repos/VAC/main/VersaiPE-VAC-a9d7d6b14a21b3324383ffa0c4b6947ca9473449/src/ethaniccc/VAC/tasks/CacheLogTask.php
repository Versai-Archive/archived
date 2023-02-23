<?php

namespace ethaniccc\VAC\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

final class CacheLogTask extends AsyncTask {

	public $log;

	public function __construct(
		public string $username,
		array $log
	) {
		$this->log = json_encode($log);
	}

	public function onRun() {
		@mkdir("plugin_data/VAC/logs");
		if (!file_exists("plugin_data/VAC/logs/{$this->username}")) {
			file_put_contents("plugin_data/VAC/logs/{$this->username}", "");
		}
		$contents = file_get_contents("plugin_data/VAC/logs/{$this->username}");
		$decoded = json_decode($contents, true);
		$dec = json_decode($this->log);
		$decoded[] = $dec;
		file_put_contents("plugin_data/VAC/logs/{$this->username}", json_encode($decoded));
	}

}