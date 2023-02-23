<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Shovel;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\generator\object\TallGrass as TallGrassObject;
use pocketmine\world\World;
use function mt_rand;

class Grass extends Opaque{

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.6, BlockToolType::SHOVEL));
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaBlocks::DIRT()->asItem()
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick(World $world, Vector3 $pos) : void{
		$lightAbove = $world->getFullLightAt($pos->x, $pos->y + 1, $pos->z);
		if($lightAbove < 4 and $world->getBlockAt($pos->x, $pos->y + 1, $pos->z)->getLightFilter() >= 2){
			//grass dies
			$ev = new BlockSpreadEvent($this, $this, VanillaBlocks::DIRT());
			$ev->call();
			if(!$ev->isCancelled()){
				$world->setBlock($pos, $ev->getNewState(), false);
			}
		}elseif($lightAbove >= 9){
			//try grass spread
			for($i = 0; $i < 4; ++$i){
				$x = mt_rand($pos->x - 1, $pos->x + 1);
				$y = mt_rand($pos->y - 3, $pos->y + 1);
				$z = mt_rand($pos->z - 1, $pos->z + 1);

				$b = $world->getBlockAt($x, $y, $z);
				if(
					!($b instanceof Dirt) or
					$b instanceof CoarseDirt or
					$world->getFullLightAt($x, $y + 1, $z) < 4 or
					$world->getBlockAt($x, $y + 1, $z)->getLightFilter() >= 2
				){
					continue;
				}

				$ev = new BlockSpreadEvent($b, $this, VanillaBlocks::GRASS());
				$ev->call();
				if(!$ev->isCancelled()){
					$world->setBlock($b->pos, $ev->getNewState(), false);
				}
			}
		}
	}

	public function onInteract(World $world, Vector3 $blockPos, Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($face !== Facing::UP){
			return false;
		}
		if($item instanceof Fertilizer){
			$item->pop();
			TallGrassObject::growGrass($world, $blockPos, new Random(mt_rand()), 8, 2);

			return true;
		}elseif($item instanceof Hoe){
			$item->applyDamage(1);
			$world->setBlock($blockPos, VanillaBlocks::FARMLAND());

			return true;
		}elseif($item instanceof Shovel and $this->getSide(Facing::UP)->getId() === BlockLegacyIds::AIR){
			$item->applyDamage(1);
			$world->setBlock($blockPos, VanillaBlocks::GRASS_PATH());

			return true;
		}

		return false;
	}
}
