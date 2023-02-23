<?php

namespace ethaniccc\VAC\detection\reach;

use ethaniccc\VAC\data\ACData;
use ethaniccc\VAC\detection\Detection;
use ethaniccc\VAC\mcprotocol\v428\PlayerAuthInputPacket;
use ethaniccc\VAC\util\AABB;
use ethaniccc\VAC\util\Math;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\utils\TextFormat;

final class ReachA extends Detection {

	private array $recentFlags = [];
	private int $lastTPIncident = -1;
	private bool $waiting = false;

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
		&& $packet->trData->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK && $this->getData()->getPlayer()->isSurvival()) {
			$locDat = $this->getData()->locMap->get($this->getData()->attackData->hitEntityId);
			if ($locDat === null || $locDat->isTeleporting()) {
				$this->lastTPIncident = $this->getData()->currentTick;
				return;
			}
			if ($this->getData()->inputMode === InputMode::TOUCHSCREEN) {
				$AABB = AABB::fromPosition($locDat->currentPos, $locDat->width, $locDat->height)->expand(0.1, 0.1, 0.1);
				$distance = $AABB->distanceFromVector($this->getData()->attackData->attackPos);
				if ($distance > $this->getOption("max_touch_dist", 3.15)) {
					if ($this->buff(1, 4) >= 2.5) {
						$violations = $this->createViolationFromLastFlag(600);
						$this->flag([
							"dist" => round($distance, 2),
							"type" => "raw"
						], $violations);
					}
					if (!isset($this->recentFlags[$this->getData()->currentTick])) {
						$this->recentFlags[$this->getData()->currentTick] = 0;
					}
					$this->recentFlags[$this->getData()->currentTick] += $violations ?? 0;
				} else {
					$this->buff(-0.01);
				}
			} else {
				$this->waiting = true;
			}
		} elseif ($packet instanceof PlayerAuthInputPacket) {
			if ($this->waiting) {
				$locDat = $this->getData()->locMap->get($this->getData()->attackData->hitEntityId);
				if ($locDat !== null) {
					$AABB = AABB::fromPosition($locDat->oldPos, $locDat->width, $locDat->height)->expand(0.1, 0.1, 0.1);
					$eyePos = $this->getData()->movementData->currentPos->add(0, $this->getData()->getPlayer()->isSneaking() ? 1.54 : 1.62);
					$results = [
						$AABB->calculateIntercept(
							$eyePos,
							$this->getData()->attackData->attackPos->add(Math::directionVectorFromValues(
								$this->getData()->movementData->lastYaw, $this->getData()->movementData->pitch
							)->multiply(7)))?->getHitVector()->distance($this->getData()->attackData->attackPos) ?? PHP_INT_MAX,
						$AABB->calculateIntercept(
							$eyePos,
							$this->getData()->attackData->attackPos->add(Math::directionVectorFromValues(
								$this->getData()->movementData->lastYaw, $this->getData()->movementData->lastPitch
							)->multiply(7)))?->getHitVector()->distance($this->getData()->attackData->attackPos) ?? PHP_INT_MAX
					];
					$violations = null;
					$distance = min($results);
					$rawDist = $AABB->distanceFromVector($this->getData()->attackData->attackPos);
					if ($distance === PHP_INT_MAX) {
						if ($rawDist > $this->getOption("max_touch_dist", 3.15)) {
							if ($this->buff(1, 10) >= 6) {
								$violations = $this->createViolationFromLastFlag(600);
								$this->flag([
									"dist" => round($rawDist, 2),
									"type" => "raw-fallback"
								], $violations);
							}
							if (!isset($this->recentFlags[$this->getData()->currentTick])) {
								$this->recentFlags[$this->getData()->currentTick] = 0;
							}
							$this->recentFlags[$this->getData()->currentTick] += $violations ?? 0;
						} else {
							$this->buff(-0.005);
						}
					} elseif ($distance >= $this->getOption("max_default_dist", 3.1) && abs($distance - $rawDist) < 0.4) {
						if ($this->buff(1, 10) >= 4) {
							$violations = $this->createViolationFromLastFlag(600);
							$this->flag([
								"dist" => round($distance, 2)
							], $violations);
						}
						if (!isset($this->recentFlags[$this->getData()->currentTick])) {
							$this->recentFlags[$this->getData()->currentTick] = 0;
						}
						$this->recentFlags[$this->getData()->currentTick] += $violations ?? 0;
					} else {
						$this->buff(-0.005);
					}
				}
				$this->waiting = false;
			}
			$current = $this->getData()->currentTick;
			$this->recentFlags = array_filter($this->recentFlags, function (float $violations, int $tick) use ($current): bool {
				return $current - $tick <= 20;
			}, ARRAY_FILTER_USE_BOTH);
			if (abs($current - $this->lastTPIncident) <= 20) {
				foreach ($this->recentFlags as $violations) {
					$this->violations -= $violations;
					$this->buff(-1);
				}
				$this->recentFlags = [];
			}
		}
	}

}