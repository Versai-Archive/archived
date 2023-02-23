<?php

declare(strict_types=1);

namespace Versai\Sessions;

use pocketmine\player\Player;
use Versai\MinigamesCore;

class SessionManager {

	public array $sessions;
	public MinigamesCore $plugin;

	public function __construct(MinigamesCore $plugin) {
		$this->plugin = $plugin;
	}

	public function registerSession(Player $player): void {
		$this->sessions[spl_object_hash($player)] = new PlayerSession($player);
	}

	public function unregisterSession(Player $player): void {
		unset($this->sessions[spl_object_hash($player)]);
	}

	public function getSession(Player $player): ?PlayerSession {
		return $this->sessions[spl_object_hash($player)] ?? null;
	}

	public function getSessions(): array {
		return $this->sessions;
	}

}