<?php

declare(strict_types=1);

namespace Skyblock\Database;

use Skyblock\Island\Island;
use Skyblock\Main;
use Skyblock\Sessions\PlayerSession;
use pocketmine\player\Player;

class Database {

	const TABLE_PLAYERS = "players";
	const TABLE_PLAYERS_SKILLS = "players_skills";
	const TABLE_ISLANDS = "islands";

	private $database;

	public function __construct() {
		$this->database = Main::getInstance()->getDirectDatabase();
	}

	public function testConnection(): bool {
		if (!$this->database->info()) {
			return false;
		}
		return true;
	}

	public function initTables() {
		/**
		 * =====================
		 * Players
		 * =====================
		 * Username
		 * XUID
		 * Level
		 * Coins
		 * Keys
		 */
		$this->database->create(self::TABLE_PLAYERS, [
			"username" => [
				"VARCHAR(32)"
			],
			"xuid" => [
				"VARCHAR(18)",
				"UNIQUE",
				"PRIMARY KEY"
			],
			"level" => [
				"INT",
				"DEFAULT 0"
			],
			"xp" => [
				"FLOAT",
				"DEFAULT 0"
			],
			"coins" => [
				"INT",
				"DEFAULT 0"
			],
			"keys" => [
				"JSON"
			],
			"invites" => [
				"JSON"
			],

		]);

		$this->database->create(self::TABLE_ISLANDS, [
			"xuid" => [
				"VARCHAR(18)",
				"PRIMARY KEY"
			],
			"username" => [
				"VARCHAR(32)"
			],
			"level" => [
				"INT",
				"DEFAULT 0"
			],
			"xp" => [
				"FLOAT",
				"DEFAULT 0"
			],
			"members" => [
				"JSON"
			],
			"spawners" => [
				"INT"
			],
			"banned" => [
				"JSON"
			],
			"locked" => [
				"BOOLEAN",
				"DEFAULT 0"
			],
			"stats" => [
				"JSON"
			],
			"settings" => [
				"JSON"
			],
			"invited" => [
				"JSON"
			]
		]);

		$this->database->create(self::TABLE_PLAYERS_SKILLS, [
			"xuid" => [
				"VARCHAR(18)",
				"PRIMARY KEY"
			],
			"username" => [
				"VARCHAR(32)"
			],
			"mining" => [
				"INT",
				"DEFAULT 0"
			],
			"woodcutting" => [
				"INT",
				"DEFAULT 0"
			],
			"farming" => [
				"INT",
				"DEFAULT 0"
			],
			"fishing" => [
				"INT",
				"DEFAULT 0"
			],
			"combat" => [
				"INT",
				"DEFAULT 0"
			],
			"mining_xp" => [
				"INT",
				"DEFAULT 0"
			],
			"woodcutting_xp" => [
				"INT",
				"DEFAULT 0"
			],
			"farming_xp" => [
				"INT",
				"DEFAULT 0"
			],
			"fishing_xp" => [
				"INT",
				"DEFAULT 0"
			],
			"combat_xp" => [
				"INT",
				"DEFAULT 0"
			]
		]);
	}

	public function updatePlayer(PlayerSession $session) {
		if ($this->playerHasData($session->getPlayer())) {
			$this->database->update(self::TABLE_PLAYERS, [
				"username" => $session->getPlayer()->getName(),
				"level" => $session->getLevel(),
				"xp" => $session->getXp(),
				"coins" => $session->getCoins(),
				"keys[JSON]" => $session->getKeys(),
			], [
				"xuid" => $session->getPlayer()->getXuid()
			]);

			$this->updateIsland($session->getIsland());
			return;
		}

		$this->database->insert(self::TABLE_PLAYERS, [
			"username" => $session->getPlayer()->getName(),
			"level" => $session->getLevel(),
			"xp" => $session->getXp(),
			"coins" => $session->getCoins(),
			"keys[JSON]" => $session->getKeys(),
			"xuid" => $session->getPlayer()->getXuid()
		], "xuid");
		$this->updateIsland($session->getIsland());
		Main::getInstance()->getLogger()->info("§aNew player - §e" . $session->getPlayer()->getName() . " §ahas been added to the database");
	}

	public function playerHasData(Player $player) {
		return $this->database->has(self::TABLE_PLAYERS, [
			"AND" => [
				"xuid" => $player->getXuid()
			]
		], [
			"xuid" => $player->getXuid()
		]);
	}

	public function playerHasIsland(Player $player): bool {
		return $this->database->has(self::TABLE_ISLANDS, [
			"AND" => [
				"xuid" => $player->getXuid()
			]
		], [
			"xuid" => $player->getXuid()
		]);
	}

	public function getIslandData(Player $player) {
		return $this->database->select(self::TABLE_ISLANDS, "*", [
			"xuid" => $player->getXuid()
		]);
	}

	public function getIslandDataOffline(string $xuid) {
		return $this->database->select(self::TABLE_ISLANDS, "*", [
			"xuid" => $xuid
		]);
	}

	public function createIsland(Island $island) {
		$this->database->insert(self::TABLE_ISLANDS, [
			"xuid" => $island->getOwner()->getXuid(),
			"username" => $island->getOwner()->getName(),
			"level" => $island->getLevel(),
			"xp" => $island->getXp(),
			"members[JSON]" => $island->getMembers(),
			"spawners" => $island->getSpawners(),
			"banned[JSON]" => $island->getBanned(),
			"locked" => $island->getLocked(),
			"stats[JSON]" => $island->getStats(),
			"settings[JSON]" => $island->getSettings(),
			"invited[JSON]" => $island->getInvited()
		], "xuid");
	}

	public function updateIsland(Island $island) {
		$this->database->update(self::TABLE_ISLANDS, [
			"xuid" => $island->getOwner()->getXuid(),
			"username" => $island->getOwner()->getName(),
			"level" => $island->getLevel(),
			"xp" => $island->getXp(),
			"members[JSON]" => $island->getMembers(),
			"spawners" => $island->getSpawners(),
			"banned[JSON]" => $island->getBanned(),
			"locked" => $island->getLocked(),
			"stats[JSON]" => $island->getStats(),
			"settings[JSON]" => $island->getSettings(),
			"invited[JSON]" => $island->getInvited()
		], [
			"xuid" => $island->getOwner()->getXuid()
		]);
	}

	public function getIslandByNameOffline(string $name) {
		return $this->database->select(self::TABLE_ISLANDS, "*", [
			"username" => $name
		]);
	}
}