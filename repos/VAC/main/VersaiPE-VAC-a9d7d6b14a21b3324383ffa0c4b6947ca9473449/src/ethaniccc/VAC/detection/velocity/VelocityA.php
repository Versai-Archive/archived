<?php

namespace ethaniccc\VAC\detection\velocity;

use ethaniccc\VAC\data\ACData;
use ethaniccc\VAC\detection\Detection;
use ethaniccc\VAC\mcprotocol\v428\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\DataPacket;

final class VelocityA extends Detection {

	private float $motion = 0;
	private float $lastMotion = -1;

	public function __construct(ACData $data) {
		parent::__construct(
			$data,
			"Velocity",
			"A",
			"Checks if the user's vertical knockback is lower than normal",
			true
		);
	}

	public function inbound(DataPacket $packet): void {
		if ($packet instanceof PlayerAuthInputPacket) {
			if ($this->getData()->movementData->ticksSinceReceivedMotion === 1) {
				$this->motion = $this->getData()->movementData->getServerMotion()->y;
			}
			if ($this->motion > 0.005) {
				$pct = max(
					($this->getData()->movementData->previousDelta->y / $this->motion) * 100,
					$this->lastMotion == -1
						? ($this->getData()->movementData->previousDelta->y / (($this->motion - 0.08) * 0.98))
						: ($this->lastMotion)
				);
				if ($pct < $this->getOption("pct", 99.99) && $pct >= 0 && !$this->getData()->movementData->isBlockAbove()
				&& $this->getData()->movementData->ticksSinceClimb > 10 && $this->getData()->movementData->ticksSinceLiquid > 10
				&& $this->getData()->movementData->ticksSinceCobweb > 10) {
					if ($this->buff(1, 6) >= 4) {
						$this->flag([
							"pct" => round($pct, 2) . "%"
						], min($this->createViolationFromLastFlag(100), 0.05));
					}
				} else {
					$this->buff(-0.2);
				}
				$this->lastMotion = $this->motion;
				$this->motion -= 0.08;
				$this->motion *= 0.98;
			} else {
				$this->lastMotion = -1;
			}
		}
	}

}