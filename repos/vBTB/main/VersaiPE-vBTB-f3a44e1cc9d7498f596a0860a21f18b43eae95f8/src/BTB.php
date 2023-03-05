<?php

declare(strict_types=1);

namespace Versai\BTB;

use Medoo\Medoo;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use Versai\BTB\Arena\Arena;
use Versai\BTB\Arena\ArenaManager;
use Versai\BTB\Database\Database;
use Versai\BTB\Queue\QueueManager;
use Versai\BTB\Queue\QueueTask;
use Versai\BTB\Sessions\SessionManager;

class BTB extends PluginBase {

	use SingletonTrait;

	private QueueManager $queueManager;

	private ArenaManager $arenaManager;

	private Database $database;

	private SessionManager $sessionManager;

	public function onEnable(): void {
		self::setInstance($this);

		$this->queueManager = new QueueManager($this, true);
		$this->arenaManager = new ArenaManager($this);

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		// 5 seconds
		$this->getScheduler()->scheduleRepeatingTask(new QueueTask($this), 20 * 5);

		$this->arenaManager->registerArenas();

		$databaseInfo = $this->getConfig()->get('database');

		$this->dirdatabase = new Medoo([
			"type" => "mysql",
			"host" => $databaseInfo["host"],
			'database' => $databaseInfo["database"],
			'username' => $databaseInfo["user"],
			'password' => $databaseInfo["password"]
		]);

		$this->database = new Database($this->dirdatabase);
		$this->database->initalizeTables();

		$this->sessionManager = new SessionManager($this);
	}

	public function getQueueManager(): QueueManager {
		return $this->queueManager;
	}

	public function getArenaManager(): ArenaManager {
		return $this->arenaManager;
	}

	public function getDatabase(): Database {
		return $this->database;
	}

	public function getSessionManager(): SessionManager {
		return $this->sessionManager;
	}
}