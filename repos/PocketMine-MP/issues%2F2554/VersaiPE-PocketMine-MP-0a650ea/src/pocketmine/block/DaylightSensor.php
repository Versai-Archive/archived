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

use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\Player;

class DaylightSensor extends Transparent{

	protected $itemId = self::DAYLIGHT_SENSOR;

	/** @var int */
	protected $power = 0;

	/** @var bool */
	protected $inverted = false;

	public function __construct(){

	}

	public function getId() : int{
		return $this->inverted ? self::DAYLIGHT_SENSOR_INVERTED : self::DAYLIGHT_SENSOR;
	}

	protected function writeStateToMeta() : int{
		return $this->power;
	}

	public function readStateFromMeta(int $meta) : void{
		$this->power = $meta;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function isInverted() : bool{
		return $this->inverted;
	}

	/**
	 * @param bool $inverted
	 *
	 * @return $this
	 */
	public function setInverted(bool $inverted = true) : self{
		$this->inverted = $inverted;
		return $this;
	}

	public function getName() : string{
		return "Daylight Sensor";
	}

	public function getHardness() : float{
		return 0.2;
	}

	public function getFuelTime() : int{
		return 300;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->trim(Facing::UP, 0.5);
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$this->inverted = !$this->inverted;
		$this->level->setBlock($this, $this);
		return true;
	}

	//TODO
}
