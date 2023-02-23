<?php

namespace ethaniccc\VAC\tasks;

use ethaniccc\VAC\data\DataHandler;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\scheduler\Task;

final class TickingTask extends Task {

	public function onRun(): void {
		foreach (DataHandler::getInstance()->all() as $data) {
			if ($data->lastLocationACKTimestamp !== -1 && count($data->queuedLocations) > 0) {
				$locations = $data->queuedLocations;
				$data->queuedLocations = [];
				$data->latencyHandler->add(function () use ($data, $locations): void {
					foreach ($locations as $packet) {
						$locDat = $data->locMap->get($packet->actorRuntimeId);
						if ($packet instanceof MoveActorAbsolutePacket) {
							$locDat?->setTeleporting($packet->flags >= 2);
						} else {
							$locDat?->setTeleporting($packet->mode === MovePlayerPacket::MODE_TELEPORT);
						}
						$locDat?->setServerPos($packet->position->subtract(0, ($locDat->isPlayer() ? 1.62 : 0), 0));
					}
				}, $data->lastLocationACKTimestamp);
			}
			$packet = new NetworkStackLatencyPacket();
			$packet->timestamp = mt_rand(1, 10000000) * 1000;
			$packet->needResponse = true;
			$data->lastLocationACKTimestamp = $packet->timestamp;
			$data->getPlayer()->getNetworkSession()->sendDataPacket($packet);
			while (($packet = array_shift($data->inboundQueue)) !== null) {
				$data->inbound($packet);
			}
			while (($packet = array_shift($data->outboundQueue)) !== null) {
				$data->outbound($packet);
			}
		}
	}

}