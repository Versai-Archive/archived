<?php

namespace ethaniccc\VAC\data\location;

use pocketmine\math\Vector3;

final class LocationData {

	public Vector3 $currentPos;
	public Vector3 $oldPos;
	public int $newPosTickIncrements = 3;
	public bool $teleporting = false;

	public function __construct(
		public float   $width,
		public float   $height,
		public int     $entityRuntimeId,
		public Vector3 $receivedPos,
		Vector3        $motion,
		public bool    $isPlayer
	) {
		$this->currentPos = clone $this->receivedPos;
		$this->oldPos = clone $this->receivedPos;
		$this->receivedPos = $this->receivedPos->add($motion->getX(), $motion->getY(), $motion->getZ());
	}

	public function setServerPos(Vector3 $pos): void {
		$this->newPosTickIncrements = 3;
		$this->receivedPos = $pos;
	}

	public function setWidth(float $width): void {
		$this->width = $width;
	}

	public function setHeight(float $height): void {
		$this->height = $height;
	}

	public function getWidth(): float {
		return $this->width;
	}

	public function getHeight(): float {
		return $this->height;
	}

	public function isPlayer(): bool {
		return $this->isPlayer;
	}

	public function tick(): void {
		if ($this->isTeleporting()) {
			$this->oldPos = clone $this->currentPos;
			$this->currentPos = clone $this->receivedPos;
		} else {
			if ($this->newPosTickIncrements > 0) {
				$diff = $this->receivedPos->subtract($this->oldPos->getX(), $this->oldPos->getY(), $this->oldPos->getZ())->divide($this->newPosTickIncrements);
				$this->oldPos = clone $this->currentPos;
				$this->currentPos = $this->oldPos->add($diff->getX(), $diff->getY(), $diff->getZ());
			}
		}
		--$this->newPosTickIncrements;
		$this->setTeleporting(false);
	}

	public function isTeleporting(): bool {
		return $this->teleporting;
	}

	public function setTeleporting(bool $teleporting = true): void {
		$this->teleporting = $teleporting;
	}

}