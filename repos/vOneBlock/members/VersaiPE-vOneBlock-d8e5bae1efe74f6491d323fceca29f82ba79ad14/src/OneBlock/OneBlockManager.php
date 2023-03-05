<?php

declare(strict_types=1);

namespace Versai\OneBlock\OneBlock;

use pocketmine\world\World;
use Versai\OneBlock\Main;

class OneBlockManager {

    private Main $plugin;

    /** @var OneBlock[] $islands */
    private array $islands = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function getIslands() {
        return $this->islands;
    }

    public function addIsland(OneBlock $island) {
        $this->islands[$island->getOwner()] = $island;
    }

    public function removeIsland(OneBlock $island) {
        unset($this->islands[$island->getOwner()]);
    }

    public function getIslandByXuid(string $xuid) {
        return $this->islands[$xuid] ?? null;
    }

    public function islandIsRegistered(string $xuid) {
        return isset($this->islands[$xuid]);
    }

	public function getIsland(World $world): ?OneBlock {
		return $this->islands[str_replace("ob-", "", $world->getFolderName())] ?? null;
	}

}