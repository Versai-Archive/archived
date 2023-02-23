<?php

declare(strict_types=1);

namespace Versai\RPGCore\Tasks;

use http\Exception\InvalidArgumentException;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use Versai\RPGCore\Entities\Zombie;
use Versai\RPGCore\Main;

class SpawnHostileMobTask extends Task {

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $worldName = $this->plugin->getConfig()->get("MainWorld");

        if (!$worldName) {
            throw new \InvalidArgumentException("The config is missing MainWorld");
        }

        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldName);

        if ($world->getDifficulty() < World::DIFFICULTY_EASY) return;
        $entities = 0;
        foreach($world->getEntities() as $entity) {
            if ($entity instanceof Zombie) {
                $entities += 1;
            }
            if ($entities >= 200) {
                continue;
            }
        }

        $chunks = [];
        foreach ($world->getPlayers() as $player) {
            foreach ($player->getUsedChunks() as $hash => $sent) {
                if ($sent) {
                    World::getXZ($hash, $chunkX, $chunkY);
                    $chunks[$hash] = $player->getWorld()->getChunk($chunkX, $chunkY);
                }
            }
        }

        foreach ($chunks as $chunk) {
            $packCenter = new Vector3(mt_rand($chunk->getX() << 4, (($chunk->getX() << 4) + 15)), mt_rand(0, $world->getMaxY() - 1), mt_rand($chunk->getZ() << 4, (($chunk->getZ() << 4) + 15)));

            if (!$world->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
                $attempts = 0;
                for ($currentPackSize = 0; $attempts <= 12 && $currentPackSize < 4; $attempts++) {
                    $x = mt_rand(-20, 20) + $packCenter->x;
                    $z = mt_rand(-20, 20) + $packCenter->z;
                    $entity = new Zombie(new Location($x + 0.5, $packCenter->y, $z + 0.5, $world, 0, 0));
                    if ($entity !== null) {
                        $currentPackSize++;
                    }
                }
            }
        }

    }
}