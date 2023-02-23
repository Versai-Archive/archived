<?php

declare(strict_types=1);

namespace Versai\RPGCore\Tasks;

use pocketmine\color\Color;
use pocketmine\scheduler\Task;
use Versai\RPGCore\Main;
use pocketmine\math\Vector3;
use pocketmine\world\particle\DustParticle;

/**
 * This will be used for the tutorial so that the player knows where to go
 */

class LineTask extends Task {

    private $plugin;
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $positions = [];
            $pos1 = $player->getLocation()->asVector3();
            $pos2 = new Vector3(0, 5, 0);

            $delX = $pos2->getX() - $pos1->getX();
            $delY = $pos2->getY() - $pos1->getY();	
            $delZ = $pos2->getZ() - $pos1->getZ();

            $points = 15;

            for ($i = 0; $i < $points; $i++) {
                $newX = $pos1->getX() + $delX / ($points - 1) * $i;
                $newY = $pos2->getY() + $delY / ($points - 1) * $i;
                $newZ = $pos1->getZ() + $delZ / ($points - 1) * $i;

                $positions[] = new Vector3($newX, $newY, $newZ);
            }

            $color = new Color(255, 120, 116);
            $particle = new DustParticle($color);

            $world = $player->getWorld();

            foreach($positions as $position) {
                $world->addParticle($position, $particle);
            }
        }
    }
}