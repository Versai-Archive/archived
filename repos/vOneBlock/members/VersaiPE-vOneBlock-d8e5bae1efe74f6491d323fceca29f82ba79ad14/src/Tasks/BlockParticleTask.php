<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Tasks;

use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\particle\WaterParticle;

class BlockParticleTask extends Task {

	public function onRun(): void {
		$players = Server::getInstance()->getOnlinePlayers();
		foreach ($players as $player) {
			$world = $player->getPosition()->getWorld();
			if (str_starts_with($world->getFolderName(), "ob-")) {
				// spawn particles
				// Math is hard
				# Amount of particles spawned
				$items = 32;
				// @var Vector3[] $points
				$points = [];
				for($i = 0; $i < $items; $i++) {
					# Radius
					$r = 0.5;
					# X Position
					$x = 256.5 + $r * cos(2 * pi() * $i / $items);

					# Y Position
					$y = 256.5 + $r * -sin(2 * pi() * $i / $items);

					array_push($points, new Vector3($x, 65.1, $y));
				}

				foreach ($points as $count => $point) {
					// We totaly did NOT just spend an hour going through all the particles, and making pp's with them
					$world->addParticle($point, new WaterParticle());
				}
			}
		}
		// WHY THE FUCK DOES THIS THING NOT WORK
		/*var_dump($names);
		foreach ($worlds as $world) {
			var_dump($worlds);
			var_dump($world->getFolderName());
			if (str_starts_with($world->getFolderName(), "ob-")) {
				// spawn particles
				// Math is hard
				# Amount of particles spawned
				$items = 32;
				// @var Vector3[] $points
				$points = [];
				for($i = 0; $i < $items; $i++) {
					# Radius
					$r = 1;
					# X Position
					$x = 2 + $r * cos(2 * pi() * $i / $items);

					# Y Position
					$y = 2 + $r * cos(2 * pi() * $i / $items);

					$points[] += new Vector3($x, 66, $y);
				}

				foreach ($points as $point) {
					var_dump("(" . $point->x . ", " . $point->y . ", " . $point->z . ")");
					$world->addParticle($point, new DustParticle(new Color(0, 200, 40)));
				}
			}
			return;
		}*/
	}

}