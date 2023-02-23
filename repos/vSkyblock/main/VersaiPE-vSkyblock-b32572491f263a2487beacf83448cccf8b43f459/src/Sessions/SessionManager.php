<?php
declare(strict_types=1);

namespace Skyblock\Sessions;

use pocketmine\player\Player;
use Skyblock\Main;
use function spl_object_hash;

class SessionManager {

	/** @var PlayerSession[] */
	private array $sessions = [];
	private Main $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function registerSession(Player $player){
		$this->sessions[spl_object_hash($player)] = new PlayerSession($player);
	}

	public function unregisterSession(Player $player){
		unset($this->sessions[spl_object_hash($player)]);
	}

	public function getSession(Player $player): ?PlayerSession{
		return $this->sessions[spl_object_hash($player)] ?? null;
	}

	public function getSessions(): array{
		return $this->sessions;
	}
}