<?php

declare(strict_types=1);

namespace Versai\BTB\Queue;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use Versai\BTB\Arena\Arena;
use Versai\BTB\BTB;
use Versai\BTB\Events\PlayerEnterQueueEvent;

class QueueManager {

	private BTB $plugin;

	/** @var Player[] */
	private array $unrankedQueue = [];

	private array $rankedQueue = [];

	public function __construct(BTB $plugin, bool $debug = false) {
		$this->plugin = $plugin;
		$this->debug = $debug;
	}

	public function getQueueUnranked() {
		return $this->unrankedQueue;
	}

	public function getQueueRanked() {
		return $this->rankedQueue;
	}

	public function addPlayerToQueue(Player $player, string $queue = "unranked"): bool {
		$logger = $this->plugin->getLogger();
		if ($queue === "unranked") {
			if ($this->debug) {
				$logger->info("Attempting to add §a" . $player->getName() . " §rto the §a" . $queue . "§r queue");
			}
			if (in_array($player, $this->unrankedQueue)) {
				if ($this->debug) {
					$logger->info("Failed to add player §a" . $player->getName() . "§r to the unranked queue! §cReason: Already in queue");
				}
				return false;
			}

			$this->unrankedQueue[] = $player;
			if ($this->debug) {
				$logger->info("Added player §a" . $player->getName() . "§r to the unranked queue");
				print_r($this->unrankedQueue);
				$logger->info("Calling §ePlayerEnterQueueEvent...");
			}
			(new PlayerEnterQueueEvent($player))->call();
			if ($this->debug) {
				$logger->info("Called §ePlayerEnterQueueEvent");
			}
			return true;
		}

		return false;
	}

	public function checkForMatches(): bool {
		if (count($this->unrankedQueue) >= 2) {
			return true;
		}
		if (count($this->rankedQueue) >= 2) {
			return true;
		}
		return false;
	}

	public function startMatch(): void {
		[$playerOne, $playerTwo] = [$this->unrankedQueue[0], $this->unrankedQueue[1]];
		$logger = $this->plugin->getLogger();
		if ($this->debug) {
			$logger->info("Attempting to start a match with players \n - §a{$playerOne->getName()}§r \n - §a {$playerTwo->getName()}");
			$logger->info("Loading map...");
		}
		$worldName = array_rand($this->plugin->getConfig()->get("maps"));
		$arenaData = $this->plugin->getConfig()->getNested("maps." . $worldName);
		if (!Server::getInstance()->getWorldManager()->isWorldLoaded($worldName)) {
			Server::getInstance()->getWorldManager()->loadWorld($worldName);
		}
		$world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
		$arena = new Arena($world, $arenaData["name"]);
		$this->plugin->getArenaManager()->createArena($arena);
		if ($this->debug) {
			$logger->info("Map was not loaded but now is...");
			$logger->info("Teleporting players");
		}
		$arena->teleportPlayers($playerOne, $playerTwo, $arenaData);
		if ($this->debug) {
			$logger->info("Players teleported");
			$logger->info("Removing players from the queue");
		}

		unset($this->unrankedQueue[0]);
		unset($this->unrankedQueue[1]);

		if ($this->debug) {
			$logger->info("Players removed from queue");
		}
	}

}