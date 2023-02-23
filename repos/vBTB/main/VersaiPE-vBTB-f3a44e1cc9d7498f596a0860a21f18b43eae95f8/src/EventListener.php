<?php

declare(strict_types=1);

namespace Versai\BTB;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use Versai\BTB\Queue\QueueManager;

class EventListener implements Listener {

	public function onChat(PlayerChatEvent $ev) {
		$message = $ev->getMessage();
		$player = $ev->getPlayer();

		if ($message === "!queue") {
			$ev->cancel();

			BTB::getInstance()->getQueueManager()->addPlayerToQueue($player);
		}
	}

	public function onJoin(PlayerJoinEvent $ev) {
		BTB::getInstance()->getSessionManager()->registerSession($ev->getPlayer());
	}

	public function onLeave(PlayerQuitEvent $ev) {
		BTB::getInstance()->getDatabase()->updatePlayer(BTB::getInstance()->getSessionManager()->getSession($ev->getPlayer()));
		BTB::getInstance()->getSessionManager()->unregisterSession($ev->getPlayer());
	}

}