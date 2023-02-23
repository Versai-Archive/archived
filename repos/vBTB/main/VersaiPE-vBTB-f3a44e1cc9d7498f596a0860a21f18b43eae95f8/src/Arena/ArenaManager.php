<?php

declare(strict_types=1);

namespace Versai\BTB\Arena;

use pocketmine\Server;
use Versai\BTB\BTB;

class ArenaManager {

	public $arenas = [];

	/** @var Arena[] */
	public $arenasInUse = [];

	public function __construct(BTB $plugin) {
		$this->plugin = $plugin;
	}

	public function registerArenas() {
		foreach ($this->plugin->getConfig()->get("maps") as $map => $name) {
			$this->arenas[] = $name;
		}
	}

	public function createArena(Arena $arena): Arena {
		$arena->generateArena();
		$this->arenasInUse[] = $arena;
		return $arena;
	}

	public function getArenasInUse() {
		return $this->arenasInUse;
	}

	public function getRandomMap(): string {
		$worlds = Server::getInstance()->getWorldManager()->getWorlds();
		$maps = BTB::getInstance()->getConfig()->get("maps");
		return array_rand($maps);
	}


}