<?php

declare(strict_types=1);

namespace Versai;

require dirname(__FILE__, 2) . '/vendor/autoload.php';

use Medoo\Medoo;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use Versai\Database\Database;
use Versai\Translator\Translator;
use Versai\Listener\Listener as EventListener;

/**
 * The main class that should be used, this will include a connection to almost all other classes and functions besides the standalones, or statics
 */
class VersaiCore extends PluginBase {

	protected Medoo $dirdatabase;

	protected Database $database;

	public function onEnable(): void {
		$this->dirdatabase = new Medoo([
			'type' => 'mysql',
			'host' => 'localhost',
			'database' => 'versai',
			'username' => 'root',
			'password' => 'root'
		]);

		$this->database = new Database($this->dirdatabase);

		// init table for global stats
		$this->dirdatabase->create("player_global_stats", [
			"username" => [
				"VARCHAR(32)",
				"NOT NULL"
			],
			"xuid" => [
				"VARCHAR(16)",
				"NOT NULL",
				"PRIMARY KEY"
			],
			"coins" => [
				"INT",
				"DEFAULT 0"
			]
		]);

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function onDisable(): void {

	}

	public function getDatabase(): Database {
		return $this->database;
	}
}