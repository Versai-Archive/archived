<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Database;

use Medoo\Medoo;
use pocketmine\player\Player;
use Versai\OneBlock\OneBlock\OneBlock;
use Versai\OneBlock\Sessions\PlayerSession;

class Database {

	private Medoo $database;

	const PLAYER_DATA = "player_data";

	const ISLAND_DATA = "island_data";

	public function __construct(Medoo $database) {
		$this->database = $database;
	}

	public function getRawDatabase(): Medoo {
		return $this->database;
	}

	public function initTables(): void {

		$this->database->create(self::PLAYER_DATA, [
			"xuid" => [
				"VARCHAR(32)",
				"NOT NULL",
				"PRIMARY KEY"
			],
			"username" => [
				"VARCHAR(64)",
				"NOT NULL"
			],
			"coins" => [
				"DECIMAL(38, 2)",
				"NOT NULL",
				"DEFAULT 100"
			],
			"kills" => [
				"INT",
				"NOT NULL",
				"DEFAULT 0"
			],
			"deaths" => [
				"INT",
				"NOT NULL",
				"DEFAULT 0"
			],
			"blocks_broken" => [
				"INT",
				"NOT NULL",
				"DEFAULT 0"
			],
			"blocks_placed" => [
				"INT",
				"NOT NULL",
				"DEFAULT 0"
			]
		]);

		$this->database->create(self::ISLAND_DATA, [
			"owner_xuid" => [
				"VARCHAR(64)",
				"NOT NULL",
				"PRIMARY KEY"
			],
			"owner_username" => [
				"VARCHAR(64)",
				"NOT NULL"
			],
			"name" => [
				"VARCHAR(64)",
				"NOT NULL"
			],
			"description" => [
				"TEXT",
				"NOT NULL"
			],
			"type" => [
				"VARCHAR(32)"
			],
			/**
			 * "TLS Gorilla": [Permissions]
			 * "lovediverse": [Permissions]
			 * "inthelittleSue": ["banned"]
			 */
			"members" => [
				"JSON",
				"NOT NULL",
			],
			"level" => [
				"INT",
				"NOT NULL",
				"DEFAULT 0"
			],
			"blocks_broken_count" => [
				"INT",
				"NOT NULL",
				"DEFAULT 0"
			],
			"blocks_broken_total" => [
				"INT",
				"NOT NULL",
				"DEFAULT 0"
			],
			"prestige" => [
				"INT",
				"NOT NULL",
				"DEFAULT 0"
			]
		]);

	}

	/**
	 * Checks if a player is in the database by XUID
	 *
	 * @param Player|string $player
	 * @return bool
	 */
	public function playerInDatabase(Player|string $player): bool {
		$xuid = $player;
		if ($player instanceof Player) {
			$xuid = $player->getXuid();
		}
		return $this->database->has(self::PLAYER_DATA, [
			"AND" => [
				"xuid" => $xuid
			]
		]);
	}

	/**
	 * @var string $player The username of the player
	 */
	public function playerInDatabaseByName(string $player): bool {
		return $this->database->has(self::PLAYER_DATA, [
			"AND" => [
				"username" => $player
			]
		]);
	}

	public function playerHasIsland(Player|string $player): bool {
		$xuid = $player;
		if ($player instanceof Player) {
			$xuid = $player->getXuid();
		}
		return $this->database->has(self::ISLAND_DATA, [
			"AND" => [
				"owner_xuid" => $xuid
			]
		]);
	}

	public function deleteIsland(string $xuid): void {
		$this->database->delete(self::ISLAND_DATA, [
			"xuid" => $xuid
		]);
	}

	public function getIslandByXuid(string $xuid) {
		return $this->database->select(self::ISLAND_DATA, "*", [
				"owner_xuid" => $xuid
		]);
	}

	public function getIslandByUsername(string $username) {
		return $this->database->select(self::ISLAND_DATA, "*", [
			"owner_username" => $username
		]);
	}

	public function getIslandMembers(string $player): array {
		return $this->database->select(self::ISLAND_DATA, [
			"members"
		],
		[
			"AND" => [
				"OR" => [
					"owner_username" => $player,
					"owner_xuid" => $player
				]
			]
		])[0] ?? [];
	}

	public function getPlayerData(string $xuid) {
		return $this->database->select(self::PLAYER_DATA, "*", [
			"xuid" => $xuid
		]);
	}

	public function getPlayerDataFromUsername(string $username): ?array {
		return $this->database->select(self::PLAYER_DATA, "*", [
			"username" => $username
		]);
	}

	public function updatePlayer(PlayerSession $session) {
		if ($this->playerInDatabase($session->getPlayer())) {
			$this->database->update(self::PLAYER_DATA, [
				"coins" => $session->getCoins(),
				"kills" => $session->getKills(),
				"deaths" => $session->getDeaths(),
				"blocks_broken" => $session->getBlocksBroken(),
				"blocks_placed" => $session->getBlocksPlaced()
			]);
			return;
		}

		$this->database->insert(self::PLAYER_DATA, [
			"xuid" => $session->getPlayer()->getXuid(),
			"username" => $session->getPlayer()->getName(),
			"coins" => $session->getCoins(),
			"kills" => $session->getKills(),
			"deaths" => $session->getDeaths(),
			"blocks_broken" => $session->getBlocksBroken(),
			"blocks_placed" => $session->getBlocksPlaced()
		]);
	}

	public function updateOfflineIsland(OneBlock $island): bool {
		if ($this->playerHasIsland($island->getOwner())) {
			$this->database->update(self::ISLAND_DATA, [
				"description" => $island->getDescription(),
				"type" => $island->getType(),
				"members" => $island->getMembersForDatabase() ?? "[]",
				"level" => $island->getLevel(),
				"blocks_broken_count" => $island->getBlocksBroken(),
				"blocks_broken_total" => $island->getBlocksBrokenTotal(),
				"prestige" => 0
			],
			[
				"owner_xuid" => $island->getOwner()
			]);
			return true;
		}
		return false;
	}

	public function updatePlayerIsland(PlayerSession $session): void {
		if ($this->playerHasIsland($session->getPlayer())) {
			if (!$session->hasIsland()) return;
			$this->database->update(self::ISLAND_DATA, [
				"owner_username" => strtolower($session->getPlayer()->getName()),
				"description" => $session->getIsland()->getDescription(),
				"type" => $session->getIsland()->getType(),
				"members" => $session->getIsland()->getMembersForDatabase() ?? "[]",
				"level" => $session->getIsland()->getLevel(),
				"blocks_broken_count" => $session->getIsland()->getBlocksBroken(),
				"blocks_broken_total" => $session->getIsland()->getBlocksBrokenTotal(),
				"prestige" => 0
			],
			[
				"owner_xuid" => $session->getPlayer()->getXuid()
			]);
			return;
		}
		if ($session->hasIsland()) {
			$this->database->insert(self::ISLAND_DATA, [
				"owner_xuid" => $session->getPlayer()->getXuid(),
				"owner_username" => strtolower($session->getPlayer()->getName()),
				"name" => "Funny Name",
				"description" => $session->getIsland()->getDescription(),
				"type" => $session->getIsland()->getType(),
				"members" => $session->getIsland()->getMembersForDatabase() ?? "[]",
				"level" => $session->getIsland()->getLevel(),
				"blocks_broken_count" => $session->getIsland()->getBlocksBroken(),
				"blocks_broken_total" => $session->getIsland()->getBlocksBrokenTotal(),
				"prestige" => 0
			]);
			return;
		}
	}
}