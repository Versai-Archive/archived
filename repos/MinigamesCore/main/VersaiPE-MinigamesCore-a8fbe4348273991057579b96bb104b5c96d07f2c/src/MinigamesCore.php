<?php

declare(strict_types=1);

namespace Versai;

use MongoDB\Driver\Session;
use pocketmine\plugin\PluginBase;
use Versai\Database\Database;
use Versai\Sessions\SessionManager;

class MinigamesCore extends PluginBase {

	private static MinigamesManager $minigamesManager;
	private static SessionManager $sessionManager;

	public function onEnable(): void {
		$this->getLogger()->info("Hello World");

		self::$minigamesManager = new MinigamesManager();
		self::$sessionManager = new SessionManager($this);
	}

	public function getDatabase(): Database {
		return VersaiCore::getDatabase();
	}

	public static function getMinigamesManager(): MinigamesManager {
		return self::$minigamesManager;
	}

	public static function getSessionManager(): SessionManager {
		return self::$sessionManager;
	}
}