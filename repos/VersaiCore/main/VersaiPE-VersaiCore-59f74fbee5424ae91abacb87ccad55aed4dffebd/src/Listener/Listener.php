<?php

declare(strict_types=1);

namespace Versai\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener as _Listener;
use Versai\VersaiCore;

class Listener implements _Listener {


	public function __construct(VersaiCore $plugin) {
		$this->plugin = $plugin;
	}

	public function onJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		$event->setJoinMessage("§7[§a+§7] §a" . $player->getName());

		$this->plugin->getDatabase()->initPlayer($player);
		var_dump($player->getSkin()->getSkinData());
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();
		$event->setQuitMessage("§7[§c-§7] " . $player->getName());
	}
	
	public function onPlayerChat(PlayerChatEvent $event) : void {

	}
}
