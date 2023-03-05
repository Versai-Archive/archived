<?php

declare(strict_types=1);

namespace Skyblock\Scoreboard;

use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

class Scoreboard {
	private string $title;
	private string $objectName;
	private int $score = 0;
	/** @var Player[] */
	private array $players = [];
	public $id;

	public function __construct(string $title, string $objectName, array $players = []) {
		$this->title = $title;
		$this->objectName = $objectName;
		$this->players = $players;
		$this->id = count(ScoreboardManager::getScoreboards()) + 1;
		ScoreboardManager::addScoreboard($this);
	}

	public function createScoreboard() {
		$this->sendDataPacket(SetDisplayObjectivePacket::create("sidebar", $this->objectName, $this->title, "dummy", 0));
	}

	public function addEntry(string $msg) {
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $this->objectName;
		$entry->type = 3;
		$entry->customName = " $msg   ";
		$entry->score = $this->score;
		$entry->scoreboardId = $this->score;
		$this->sendDataPacket(SetScorePacket::create(0, [$this->score => $entry]));
		$this->score++;
	}

	public function removeScoreboard() {
		$this->sendDataPacket(RemoveObjectivePacket::create($this->objectName));
	}

	private function sendDataPacket(ClientboundPacket $packet) {
		foreach ($this->players as $player) {
			$player->getNetworkSession()->sendDataPacket($packet);
		}
	}

	public function getPlayers(): array {
		return $this->players;
	}

	public function addPlayer(Player $player) {
		$this->players[] = $player;
	}

	public function removePlayer(Player $player) {
		unset($this->players[array_search($player, $this->players)]);
	}
}