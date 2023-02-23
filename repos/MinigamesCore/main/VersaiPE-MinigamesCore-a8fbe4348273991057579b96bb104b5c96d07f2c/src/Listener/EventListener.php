<?php

declare(strict_types=1);

namespace Versai\Listener;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use Versai\Listener\Listener;
use Versai\MinigamesCore;

class EventListener extends Listener {

	public function onPacketRecival(DataPacketReceiveEvent $ev) {
		$packet = $ev->getPacket();
		if ($packet instanceof PlayerAuthInputPacket) {
			$player = $ev->getOrigin()->getPlayer();
			$input = $packet->getInputMode();
			MinigamesCore::getSessionManager()->getSession($player)->setInput($input);
		}
	}

}