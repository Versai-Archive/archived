<?php

namespace ethaniccc\VAC\detection\reach;

use ethaniccc\VAC\data\ACData;
use ethaniccc\VAC\detection\Detection;
use ethaniccc\VAC\util\AABB;
use ethaniccc\VAC\util\Math;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;

final class ReachA extends Detection {

	public function __construct(ACData $data) {
		parent::__construct(
			$data,
			"Reach",
			"A",
			"Checks if the user has a valid attacking range"
		);
	}

	public function inbound(DataPacket $packet): void {
		if ($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData
			&& $packet->trData->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK) {
			$locDat = $this->getData()->locMap->get($this->getData()->attackData->hitEntityId);
			if ($locDat === null || $locDat->isTeleporting()) {
				return;
			}
			$AABB = AABB::fromPosition($locDat->currentPos, $locDat->width, $locDat->height)->expand(0.1, 0.1, 0.1);
			if ($this->getData()->inputMode === InputMode::TOUCHSCREEN) {
				$distance = $AABB->distanceFromVector($this->getData()->attackData->attackPos);
				if ($distance > $this->getOption("max_touch_dist", 3.15)) {
					if ($this->buff(1, 4) >= 2.5) {
						$this->flag([
							"dist" => round($distance, 2)
						], $this->createViolationFromLastFlag(600));
					}
				} else {
					$this->buff(-0.01);
				}
			} else {
				$directionVector = Math::directionVectorFromValues(
					$this->getData()->yaw, $this->getData()->pitch
				)->multiply(20);
				$raycast = $AABB->calculateIntercept(
					$this->getData()->attackData->attackPos,
					$this->getData()->attackData->attackPos->add($directionVector->getX(), $directionVector->getY(), $directionVector->getZ())
				);
				if ($raycast !== null) {
					$distance = $this->getData()->attackData->attackPos->distance($raycast->getHitVector());
					if ($distance > $this->getOption("max_default_dist", 3.1) && $distance < 20) {
						if ($this->buff(1, 10) >= 6) {
							$this->flag([
								"dist" => round($distance, 2)
							], $this->createViolationFromLastFlag(200));
						}
					} else {
						$this->buff(-0.005);
					}
				} else {
					$distance = $AABB->distanceFromVector($this->getData()->attackData->attackPos);
					if ($distance > $this->getOption("max_touch_dist", 3.15)) {
						if ($this->buff(1, 10) >= 6) {
							$this->flag([
								"dist" => round($distance, 2)
							], $this->createViolationFromLastFlag(600));
						}
					} else {
						$this->buff(-0.01);
					}
				}
			}
		}
	}

}