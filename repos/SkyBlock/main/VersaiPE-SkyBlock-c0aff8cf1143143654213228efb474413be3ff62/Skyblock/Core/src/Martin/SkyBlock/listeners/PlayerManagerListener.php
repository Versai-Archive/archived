<?php


namespace Martin\SkyBlock\listeners;


use Martin\SkyBlock\Loader;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class PlayerManagerListener implements Listener{
	private Loader $loader;

	public function __construct(Loader $loader){
		$this->loader = $loader;
	}

	public function onJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$this->getLoader()->getPlayerManager()->addPlayer($player);
		$event->setJoinMessage("");
		$event->getPlayer()->sendTitle("Welcome to SkyBlock");
		$player->teleport($player->getServer()->getDefaultLevel()->getSpawnLocation());
		$event->getPlayer()->getLevelNonNull()->broadcastLevelEvent($event->getPlayer()->add(0, $event->getPlayer()->getEyeHeight()), LevelEventPacket::EVENT_SOUND_BLAZE_SHOOT);
	}

	public function getLoader() : Loader{
		return $this->loader;
	}

	public function onQuit(PlayerQuitEvent $event) : void{
		$this->getLoader()->getPlayerManager()->removePlayer($event->getPlayer());
	}
}