<?php

declare(strict_types=1);

namespace Versai\Tasks;

use pocketmine\color\Color;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\world\particle\DustParticle;

class SpawnParticlesTask extends CustomRepeatingTask {

	public function onRun(): void {
		$world = Server::getInstance()->getWorldManager()->getDefaultWorld();
		$spawn = $world->getSpawnLocation();

		$r = rand(1, 300);
		$g = rand(1, 300);
		$b = rand(1, 300);

		$x = $spawn->getX();
		$y = $spawn->getY();
		$z = $spawn->getZ();

		$center = new Vector3($x + 0.5, $y + 0.5, $z + 0.5);
		$particle = new DustParticle(new Color($r, $g, $b, 1));

		for ($yaw = 0, $y = $center->y; $y < $center->y + 4; $yaw += (M_PI * 2) / 20, $y += 1 / 20) {
			$x = -sin($yaw) + $center->x;
			$z = cos($yaw) + $center->z;
			$world->addParticle(new Vector3($x, $y, $z), $particle);
		}
	}

}