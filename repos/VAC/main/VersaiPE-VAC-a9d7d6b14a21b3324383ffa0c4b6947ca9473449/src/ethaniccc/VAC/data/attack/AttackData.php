<?php

namespace ethaniccc\VAC\data\attack;

use pocketmine\math\Vector3;

final class AttackData {

	public int $hitEntityId = 0;
	public int $lastAttackTick = 0;
	public Vector3 $attackPos;

	public function set(int $entityRuntimeId, int $tick, Vector3 $attackPos): void {
		$this->hitEntityId = $entityRuntimeId;
		$this->lastAttackTick = $tick;
		$this->attackPos = $attackPos;
	}

}