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

/**
 * All the Tile classes and related classes
 */

namespace pocketmine\block\tile;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\world\Position;
use pocketmine\world\World;
use function get_class;

abstract class Tile extends Position{

	public const TAG_ID = "id";
	public const TAG_X = "x";
	public const TAG_Y = "y";
	public const TAG_Z = "z";

	/** @var string */
	public $name = "";
	/** @var bool */
	public $closed = false;
	/** @var TimingsHandler */
	protected $timings;

	public function __construct(World $world, Vector3 $pos){
		$this->timings = Timings::getTileEntityTimings($this);
		parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $world);
	}

	/**
	 * @internal
	 * Reads additional data from the CompoundTag on tile creation.
	 *
	 * @param CompoundTag $nbt
	 */
	abstract public function readSaveData(CompoundTag $nbt) : void;

	/**
	 * Writes additional save data to a CompoundTag, not including generic things like ID and coordinates.
	 *
	 * @param CompoundTag $nbt
	 */
	abstract protected function writeSaveData(CompoundTag $nbt) : void;

	public function saveNBT() : CompoundTag{
		$nbt = CompoundTag::create()
			->setString(self::TAG_ID, TileFactory::getSaveId(get_class($this)))
			->setInt(self::TAG_X, $this->x)
			->setInt(self::TAG_Y, $this->y)
			->setInt(self::TAG_Z, $this->z);
		$this->writeSaveData($nbt);

		return $nbt;
	}

	public function getCleanedNBT() : ?CompoundTag{
		$this->writeSaveData($tag = new CompoundTag());
		return $tag->getCount() > 0 ? $tag : null;
	}

	/**
	 * @internal
	 *
	 * @param Item $item
	 *
	 * @throws \RuntimeException
	 */
	public function copyDataFromItem(Item $item) : void{
		if($item->hasCustomBlockData()){ //TODO: check item root tag (MCPE doesn't use BlockEntityTag)
			$this->readSaveData($item->getCustomBlockData());
		}
	}

	/**
	 * @return Block
	 */
	public function getBlock() : Block{
		return $this->world->getBlockAt($this->x, $this->y, $this->z);
	}

	public function isClosed() : bool{
		return $this->closed;
	}

	public function __destruct(){
		$this->close();
	}

	/**
	 * Called when the tile's block is destroyed.
	 */
	final public function onBlockDestroyed() : void{
		$this->onBlockDestroyedHook();
		$this->close();
	}

	/**
	 * Override this method to do actions you need to do when this tile is destroyed due to block being broken.
	 */
	protected function onBlockDestroyedHook() : void{

	}

	public function close() : void{
		if(!$this->closed){
			$this->closed = true;

			if($this->isValid()){
				$this->world->removeTile($this);
				$this->setWorld(null);
			}
		}
	}

	public function getName() : string{
		return $this->name;
	}
}
