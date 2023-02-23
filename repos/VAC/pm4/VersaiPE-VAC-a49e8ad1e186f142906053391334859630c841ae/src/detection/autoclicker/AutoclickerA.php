<?php

namespace ethaniccc\VAC\detection\autoclicker;

use ethaniccc\VAC\data\ACData;
use ethaniccc\VAC\detection\Detection;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

final class AutoclickerA extends Detection {

	public function __construct(ACData $data) {
		parent::__construct(
			$data,
			"Autoclicker",
			"A",
			"Checks if the user is clicking faster than a given threshold"
		);
	}

	public function inbound(DataPacket $packet): void {
		if (($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData && $packet->trData->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK) || ($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE)) {
			if ($this->getData()->clickData->cps > $this->getOption("max_cps", 23)) {
				if ($this->buff() >= 2) {
					$this->flag(
						["cps" => $this->getData()->clickData->cps],
						$this->createViolationFromLastFlag(40)
					);
				}
			} else {
				$this->buff(-0.05);
			}
		}
	}

}