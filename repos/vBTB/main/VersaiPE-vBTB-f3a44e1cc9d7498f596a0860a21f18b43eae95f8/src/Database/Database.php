<?php

declare(strict_types=1);

namespace Versai\BTB\Database;

use Medoo\Medoo;
use pocketmine\player\Player;
use Versai\BTB\Sessions\PlayerSession;

class Database {

	const TABLE_STATS = "player_btb_stats";

	private Medoo $database;

	public function __construct(Medoo $database) {
		$this->database = $database;
	}

	public function initalizeTables(): void {
		$this->database->create(self::TABLE_STATS, [
			"xuid" => [
				"VARCHAR(16)",
				"NOT NULL",
				"PRIMARY KEY"
			],
			"username" => [
				"VARCHAR(64)",
				"NOT NULL"
			],
			"kills" => [
				"INT",
				"DEFAULT 0"
			],
			"deaths" => [
				"INT",
				"DEFAULT 0"
			],
			"coins" => [
				"INT",
				"DEFAULT 0"
			],
			"beds_broke" => [
				"INT",
				"DEFAULT 0"
			],
			"level" => [
				"INT",
				"DEFAULT 0"
			],
			"experience" => [
				"INT",
				"DEFAULT 0"
			],
			"elo" => [
				"INT",
				"DEFAULT 250"
			],
			"ranked_wins" => [
				"INT",
				"DEFAULT 0"
			],
			"ranked_losses" => [
				"INT",
				"DEFAULT 0"
			],
			"unranked_wins" => [
				"INT",
				"DEFAULT 0"
			],
			"unranked_losses" => [
				"INT",
				"DEFAULT 0"
			]
		]);
	}

	public function getPlayersData(Player|string $player) {
		if ($player instanceof Player) {
			return $this->database->get(self::TABLE_STATS, ["*"], [
				"xuid" => $player->getXuid()
			]);
		}
		return $this->database->get(self::TABLE_STATS, ["*"], [
			"xuid" => $player
		]);
	}

	public function playerIsInDatabase(Player|string $player): bool {
		return $this->database->has(self::TABLE_STATS, ["xuid" => $player->getXuid() ?? $player]);
	}

	public function initalizePlayer(Player $player): bool {
		if ($this->playerIsInDatabase($player)) return false;
		$this->database->insert(self::TABLE_STATS,
			[
				"xuid" => $player->getXuid(),
				"username" => $player->getName()
			]
		);
		return true;
	}

	public function updatePlayer(PlayerSession $player) {
		if (!$this->playerIsInDatabase($player->getPlayer())) {
			$this->initalizePlayer($player->getPlayer());
		}
		$this->database->update(self::TABLE_STATS,
		[
			"username" => $player->getPlayer()->getName(),
			"kills" => $player->getKills(),
			"deaths" => $player->getDeaths(),
			"coins" => $player->getCoins(),
			"beds_broke" => $player->getBedsBroken(),
			"level" => $player->getLevel(),
			"experience" => $player->getExperience(),
			"elo" => $player->getElo(),
			"ranked_wins" => $player->getRankedWins(),
			"ranked_losses" => $player->getRankedLosses(),
			"unranked_wins" => $player->getUnrankedWins(),
			"unranked_losses" => $player->getUnrankedLosses()
		]);
	}
}