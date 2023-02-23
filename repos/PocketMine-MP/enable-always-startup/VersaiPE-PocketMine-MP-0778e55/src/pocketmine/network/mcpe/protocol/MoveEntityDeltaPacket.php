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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\utils\BinaryDataException;

class MoveEntityDeltaPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::MOVE_ENTITY_DELTA_PACKET;

	public const FLAG_HAS_X = 0x01;
	public const FLAG_HAS_Y = 0x02;
	public const FLAG_HAS_Z = 0x04;
	public const FLAG_HAS_ROT_X = 0x08;
	public const FLAG_HAS_ROT_Y = 0x10;
	public const FLAG_HAS_ROT_Z = 0x20;

	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $flags;
	/** @var int */
	public $xDiff = 0;
	/** @var int */
	public $yDiff = 0;
	/** @var int */
	public $zDiff = 0;
	/** @var float */
	public $xRot = 0.0;
	/** @var float */
	public $yRot = 0.0;
	/** @var float */
	public $zRot = 0.0;

	/**
	 * @param int $flag
	 *
	 * @return int
	 * @throws BinaryDataException
	 */
	private function maybeReadCoord(int $flag) : int{
		if($this->flags & $flag){
			return $this->getVarInt();
		}
		return 0;
	}

	/**
	 * @param int $flag
	 *
	 * @return float
	 * @throws BinaryDataException
	 */
	private function maybeReadRotation(int $flag) : float{
		if($this->flags & $flag){
			return $this->getByteRotation();
		}
		return 0.0;
	}

	protected function decodePayload() : void{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->flags = $this->getByte();
		$this->xDiff = $this->maybeReadCoord(self::FLAG_HAS_X);
		$this->yDiff = $this->maybeReadCoord(self::FLAG_HAS_Y);
		$this->zDiff = $this->maybeReadCoord(self::FLAG_HAS_Z);
		$this->xRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_X);
		$this->yRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_Y);
		$this->zRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_Z);
	}

	private function maybeWriteCoord(int $flag, int $val) : void{
		if($this->flags & $flag){
			$this->putVarInt($val);
		}
	}

	private function maybeWriteRotation(int $flag, float $val) : void{
		if($this->flags & $flag){
			$this->putByteRotation($val);
		}
	}

	protected function encodePayload() : void{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putByte($this->flags);
		$this->maybeWriteCoord(self::FLAG_HAS_X, $this->xDiff);
		$this->maybeWriteCoord(self::FLAG_HAS_Y, $this->yDiff);
		$this->maybeWriteCoord(self::FLAG_HAS_Z, $this->zDiff);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_X, $this->xRot);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_Y, $this->yRot);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_Z, $this->zRot);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleMoveEntityDelta($this);
	}
}
