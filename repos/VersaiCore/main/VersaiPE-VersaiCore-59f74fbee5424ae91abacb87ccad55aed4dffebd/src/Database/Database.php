<?php

declare(strict_types=1);

namespace Versai\Database;

use Medoo\Medoo;
use pocketmine\player\Player;

class Database {

	protected $db;

	public function __construct(Medoo $database) {
		$this->db = $database;
	}

	//TODO: Put all of the player functions in here so that they are easily accessible
	public function initPlayer(Player $player): void {
		if ($this->playerIsInDatabase($player)) return;
		$this->db->insert("player_global_stats", [
			"xuid" => $player->getXuid(),
			"username" => $player->getName(),
			"coins" => 0
		]);
	}

	public function playerIsInDatabase(Player|string $player): bool {
		return $this->db->has("player_global_stats", [
			"AND" => [
				"xuid" => $player->getXuid() ?? $player
			]
		]);
	}

	public function getPlayer(Player|string $player): ?array {
		if (!$this->playerIsInDatabase($player)) return null;
		return $this->db->get("player_global_stats", ["*"], ["xuid" => ($player->getXuid() ?? $player)]);
	}
}