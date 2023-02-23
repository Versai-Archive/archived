<?php

namespace ethaniccc\VAC\detection\killaura;

use ethaniccc\VAC\data\ACData;
use ethaniccc\VAC\detection\Detection;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;

final class KillauraA extends Detection {

	private int $lastSwingTick = 0;

	public function __construct(ACData $data) {
		parent::__construct(
			$data,
			"Killaura",
			"A",
			"Checks if the user is attacking without swinging their arm (this pattern is common with Toolbox Killaura)"
		);
	}

	public function inbound(DataPacket $packet): void {
		if ($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData
		&& $packet->trData->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK) {
			if (($diff = $this->getData()->currentTick - $this->lastSwingTick) > 3 && $this->lastSwingTick !== 0) {
				if ($this->buff() > 1) {
					$this->flag([
						"td" => $diff
					], $this->createViolationFromLastFlag(600));
				}
			} else {
				$this->buff(-0.01);
			}
		} elseif ($packet instanceof AnimatePacket && $packet->action === AnimatePacket::ACTION_SWING_ARM) {
			$this->lastSwingTick = $this->getData()->currentTick;
		}
	}

}