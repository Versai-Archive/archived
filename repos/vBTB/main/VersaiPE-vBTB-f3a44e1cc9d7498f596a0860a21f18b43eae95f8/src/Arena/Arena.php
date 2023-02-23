<?php

declare(strict_types=1);

namespace Versai\BTB\Arena;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\world\Position;
use pocketmine\world\World;
use Versai\BTB\BTB;

class Arena {

	public string $id;
	public World $world;
	public string $name;

	public function __construct(World $world, string $name) {
		$this->id = $this->generateId();
		$this->world = $world;
		$this->name = $name;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getWorld(): World {
		return $this->world;
	}

	public function generateArena(): void {
		$folder = Server::getInstance()->getDataPath() . "/worlds/";

		$map = $this->world->getFolderName();

		$newWorldName = $map . "-" . $this->id;

		$worldLoaded = $this->world->isLoaded();

		if ($worldLoaded) {
			Server::getInstance()->getWorldManager()->unloadWorld($this->world);
		}

		mkdir($folder . $newWorldName);

		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder . $map, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);

		foreach($files as $fileInfo) {
			if($filePath = $fileInfo->getRealPath()) {
				if($fileInfo->isFile()) {
					@copy($filePath, str_replace($map, $newWorldName, $filePath));
				} else {
					mkdir(str_replace($map, $newWorldName, $filePath));
				}
			}
		}
	}

	public function teleportPlayers(Player $playerOne, Player $playerTwo, array $arenaInfo) {
		$coords = $arenaInfo["spawns"];
		$playerOne->teleport(new Position($coords[0][0], $coords[0][1], $coords[0][2], $this->getGeneratedWorld()));
		$playerTwo->teleport(new Position($coords[1][0], $coords[1][1], $coords[1][2], $this->getGeneratedWorld()));
	}

	public function getGeneratedWorld(): World {
		$manager = Server::getInstance()->getWorldManager();

		$name = $this->world->getFolderName() . "-" . $this->id;

		if ($manager->isWorldLoaded($name)) {
			return $manager->getWorldByName($name);
		} else {
			$manager->loadWorld($name);
			return $manager->getWorldByName($name);
		}
	}

	private function generateId(): string {
		$bytes = random_bytes(8);
		return bin2hex($bytes);
	}

	/**
	 * TODO: Teleport players to world
	 * TODO: Remove World
	 * TODO: Remove players
	 * TODO: Get spawns
	 */

}