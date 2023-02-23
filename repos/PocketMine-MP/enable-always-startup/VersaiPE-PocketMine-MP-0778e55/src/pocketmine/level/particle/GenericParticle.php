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

namespace pocketmine\level\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class GenericParticle implements Particle{
	/** @var int */
	protected $id;
	/** @var int */
	protected $data;

	public function __construct(int $id, int $data = 0){
		$this->id = $id & 0xFFF;
		$this->data = $data;
	}

	public function encode(Vector3 $pos){
		$pk = new LevelEventPacket;
		$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | $this->id;
		$pk->position = $pos;
		$pk->data = $this->data;

		return $pk;
	}
}
