<?php

declare(strict_types=1);

namespace Versai\Session;

use pocketmine\player\Player;

class SessionManager {

	private array $sessions = [];

	public function __construct() {
		// NOOP
	}

	public function getSessions(): array {
		return $this->sessions;
	}

	public function getSession(Player $player): ?PlayerSession {
		return $this->sessions[spl_object_hash($player)] ?? null;
	}

	public function registerSession(Player $player): void {
		$this->sessions[spl_object_hash($player)] = new PlayerSession($player);
	}

	public function unregisterSession(Player $player) {
		unset($this->sessions[spl_object_hash($player)]);
	}

}