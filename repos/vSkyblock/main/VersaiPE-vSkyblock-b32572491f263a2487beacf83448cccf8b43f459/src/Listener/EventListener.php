<?php

declare(strict_types=1);

namespace Skyblock\Listener;

use Skyblock\Island\Island;
use Skyblock\Main;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener {

	public function onJoin(PlayerJoinEvent $ev) {
		Main::getInstance()->getSessionManager()->registerSession($ev->getPlayer());
		Main::getInstance()->getSessionManager()->getSession($ev->getPlayer())->setIsland(new Island($ev->getPlayer()));
	}

	public function onLeave(PlayerQuitEvent $ev) {
		$db = Main::getInstance()->getDatabase();
		$db->updatePlayer(Main::getInstance()->getSessionManager()->getSession($ev->getPlayer()));
		if ($db->playerHasIsland($ev->getPlayer())) {
			$db->updateIsland(Main::getInstance()->getSessionManager()->getSession($ev->getPlayer())->getIsland());
		} else {
			$db->createIsland(Main::getInstance()->getSessionManager()->getSession($ev->getPlayer())->getIsland());
		}
		Main::getInstance()->getSessionManager()->unregisterSession($ev->getPlayer());
	}

	public function onBlockBreak(BlockBreakEvent $ev) {
		$player = $ev->getPlayer();
		$session = Main::getInstance()->getSessionManager()->getSession($player);
		if ($session->getIsland()->getWorld() === $ev->getBlock()->getPosition()->getWorld()) {
			return;
		}

		$ev->cancel();
	}

	public function onChat(PlayerChatEvent $event) {
		$player = $event->getPlayer();
		$session = Main::getInstance()->getSessionManager()->getSession($player);
	}
}