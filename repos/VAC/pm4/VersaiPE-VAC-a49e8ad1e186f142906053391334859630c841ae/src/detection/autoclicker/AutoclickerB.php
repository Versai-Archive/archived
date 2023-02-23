<?php

namespace ethaniccc\VAC\detection\autoclicker;

use ethaniccc\VAC\data\ACData;
use ethaniccc\VAC\detection\Detection;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

final class AutoclickerB extends Detection {

	private array $samples = [];

	public function __construct(ACData $data) {
		parent::__construct(
			$data,
			"Autoclicker",
			"B",
			"Checks if the user has high CPS without double clicking"
		);
	}

	public function inbound(DataPacket $packet): void {
		if (($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData && $packet->trData->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK) || ($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE)) {
			if ($this->getData()->clickData->delta < 5) {
				$this->samples[] = $this->getData()->clickData->delta;
				if (count($this->samples) === 20) {
					if ($this->getData()->clickData->cps >= 16 && !in_array(0, $this->samples, true)) {
						$this->flag([
							"cps" => $this->getData()->clickData->cps
						], $this->createViolationFromLastFlag(200));
					}
					$this->samples = [];
				}
			}
		}
	}

}