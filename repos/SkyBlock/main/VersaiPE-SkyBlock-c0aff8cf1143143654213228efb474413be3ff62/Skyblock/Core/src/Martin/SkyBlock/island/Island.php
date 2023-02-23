<?php


namespace Martin\SkyBlock\island;


use Martin\SkyBlock\Loader;
use pocketmine\math\Vector3;
use pocketmine\OfflinePlayer;
use pocketmine\Player;

class Island{
	public const DEFAULT_HELPER_SIZE = 4;

	private OfflinePlayer $owner;

	/** @var OfflinePlayer[] */
	private array $helpers = [];

	/** @var Player[] */
	private array $visitors = [];

	private int $helperSize = self::DEFAULT_HELPER_SIZE;

	public function __construct(Loader $loader){

	}

	public function getSpawnLocation() : Vector3{
		return new Vector3(0, 0, 0);
	}
}