<?php


namespace Martin\SkyBlock\listeners;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class DamageListener implements Listener{
	public function onPlayerDamageByVoid(EntityDamageEvent $event) : void{
		$player = $event->getEntity();
		if(!$player instanceof Player){
			return;
		}

		if($event->getCause() !== EntityDamageEvent::CAUSE_VOID){
			return;
		}

		$event->setCancelled();
		$player->teleport($player->getLevel()->getSpawnLocation());
	}
}