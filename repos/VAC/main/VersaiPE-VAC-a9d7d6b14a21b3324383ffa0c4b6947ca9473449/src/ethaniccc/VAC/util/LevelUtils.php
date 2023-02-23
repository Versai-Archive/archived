<?php

namespace ethaniccc\VAC\util;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;

final class LevelUtils {

	/**
	 * @param Level|null $world
	 * @param AxisAlignedBB $bb
	 * @param bool $targetFirst
	 * @return Block[]
	 */
	public static function getCollisionBlocks(?Level $world, AxisAlignedBB $bb, bool $targetFirst): array {
		if ($world === null) {
			return [];
		}
		$minX = (int)floor($bb->minX - 1);
		$minY = (int)floor($bb->minY - 1);
		$minZ = (int)floor($bb->minZ - 1);
		$maxX = (int)floor($bb->maxX + 1);
		$maxY = (int)floor($bb->maxY + 1);
		$maxZ = (int)floor($bb->maxZ + 1);

		$collides = [];

		if ($targetFirst) {
			for ($z = $minZ; $z <= $maxZ; ++$z) {
				for ($x = $minX; $x <= $maxX; ++$x) {
					for ($y = $minY; $y <= $maxY; ++$y) {
						$block = $world->getBlockAt($x, $y, $z);
						if ($block->collidesWithBB($bb)) {
							return [$block];
						}
					}
				}
			}
		} else {
			for ($z = $minZ; $z <= $maxZ; ++$z) {
				for ($x = $minX; $x <= $maxX; ++$x) {
					for ($y = $minY; $y <= $maxY; ++$y) {
						$block = $world->getBlockAt($x, $y, $z);
						if ($block->collidesWithBB($bb)) {
							$collides[] = $block;
						}
					}
				}
			}
		}

		return $collides;
	}

}