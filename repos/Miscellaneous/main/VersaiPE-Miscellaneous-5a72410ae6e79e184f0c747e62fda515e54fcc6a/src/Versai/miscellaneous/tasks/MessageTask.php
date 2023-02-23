<?php
declare(strict_types=1);

namespace Versai\miscellaneous\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use function array_rand;
use function str_replace;
use function count;

class MessageTask extends Task {

	private Server $server;
	private array $config;

	public function __construct(Server $server, array $config) {
		$this->server = $server;
		$this->config = $config;
	}

	public function onRun(): void {
		$this->server->getNetwork()->setName($this->config["motd"][array_rand($this->config["motd"])]);
		$message = $this->config["broadcasts"][array_rand($this->config["broadcasts"])];
		$message = str_replace(["{online}", "{max}"], [count($this->server->getOnlinePlayers()), $this->server->getMaxPlayers()], $message);
		foreach ($this->server->getOnlinePlayers() as $onlinePlayer) {
			$onlinePlayer->sendMessage(str_replace(["{player}",], [$onlinePlayer->getName()], $message));
		}
	}
}