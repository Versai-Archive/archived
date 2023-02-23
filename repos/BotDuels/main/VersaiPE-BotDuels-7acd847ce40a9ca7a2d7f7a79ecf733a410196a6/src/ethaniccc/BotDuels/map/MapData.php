<?php

namespace ethaniccc\BotDuels\map;

use pocketmine\math\Vector3;

final class MapData {

	/** @var string - The name of the world */
	public $name;
	/** @var string[] - Creators of the map */
	public $authors = [];
	/** @var string - Path to the map */
	public $path;
	/** @var Vector3 - Vector on the map where the player should spawn. */
	public $playerSpawnPosition;
	/** @var Vector3 - Vector on the map where the bot should spawn. */
	public $botSpawnPosition;

	public function __construct(string $name, array $authors, string $path, Vector3 $playerSpawnPosition, Vector3 $botSpawnPosition) {
		$this->name = $name;
		$this->authors = $authors;
		$this->path = $path;
		$this->playerSpawnPosition = $playerSpawnPosition;
		$this->botSpawnPosition = $botSpawnPosition;
	}

}