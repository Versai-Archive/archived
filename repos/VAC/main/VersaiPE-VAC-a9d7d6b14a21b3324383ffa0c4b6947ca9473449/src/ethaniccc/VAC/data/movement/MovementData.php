<?php

namespace ethaniccc\VAC\data\movement;

use pocketmine\math\Vector3;

final class MovementData {

	public Vector3 $currentPos;
	public Vector3 $lastPos;

	public Vector3 $currentDelta;
	public Vector3 $previousDelta;
	public Vector3 $lastClientPredictionDelta;

	public int $ticksSinceReceivedMotion = 0;
	public Vector3 $serverMotion;

	public int $ticksSinceCobweb = 0;
	public int $ticksSinceLiquid = 0;
	public int $ticksSinceClimb = 0;

	public bool $hasBlockAbove = false;

	public float $moveForward = 0.0;
	public float $moveStrafe = 0.0;

	public float $yaw = 0.0;
	public float $pitch = 0.0;
	public float $lastYaw = 0.0;
	public float $lastPitch = 0.0;

	public function __construct() {
		$zero = new Vector3(0, 0, 0);
		$this->currentPos = clone $zero;
		$this->lastPos = clone $zero;
		$this->currentDelta = clone $zero;
		$this->previousDelta = clone $zero;
		$this->lastClientPredictionDelta = clone $zero;
		$this->serverMotion = clone $zero;
	}

	public function getForward(): float {
		return $this->moveForward;
	}

	public function getStrafe(): float {
		return $this->moveStrafe;
	}

	public function setForward(float $forward): void {
		$this->moveForward = $forward;
	}

	public function setStrafe(float $strafe): void {
		$this->moveStrafe = $strafe;
	}

	public function isBlockAbove(): bool {
		return $this->hasBlockAbove;
	}

	public function setCurrentPos(Vector3 $pos): void {
		$this->lastPos = clone $this->currentPos;
		$this->currentPos = $pos;
		$this->previousDelta = clone $this->currentDelta;
		$this->currentDelta = $this->currentPos->subtract($this->lastPos);
		$this->ticksSinceClimb++;
		$this->ticksSinceCobweb++;
		$this->ticksSinceLiquid++;
		$this->ticksSinceReceivedMotion++;
	}

	public function getServerMotion(): Vector3 {
		return $this->serverMotion;
	}

	public function setServerMotion(Vector3 $motion): void {
		$this->ticksSinceReceivedMotion = 0;
		$this->serverMotion = $motion;
	}

}