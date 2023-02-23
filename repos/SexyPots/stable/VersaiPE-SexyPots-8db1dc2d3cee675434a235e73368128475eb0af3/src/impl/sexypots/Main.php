<?php

namespace impl\sexypots;

use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onProjectileHitEntity(ProjectileHitEntityEvent $event) : void{
        $projectile = $event->getEntity();
        $hit = $event->getEntityHit();
        if($projectile instanceof SplashPotion && $hit->isAlive() && $projectile->getPotionId() === 22 && $hit instanceof Player){
            $randHealth = mt_rand(4, 6);
            // $hit->sendMessage('added extra health ' . $randHealth);
            $hit->setHealth(min($randHealth + $hit->getHealth(), $hit->getMaxHealth()));
        }
    }

    public function onProjectileHitBlock(ProjectileHitBlockEvent $event) : void{
        $projectile = $event->getEntity();
        $owner = $projectile->getOwningEntity();
        if($owner instanceof Player && $owner->isAlive() && $projectile instanceof SplashPotion && $projectile->getPotionId() === 22){
            if($event->getRayTraceResult()->getHitVector()->distanceSquared($owner->asVector3()) < 6.25){
                $randHealth = mt_rand(2, 4);
                // $hit->sendMessage('added extra health ' . $randHealth);
                $owner->setHealth(min($randHealth + $owner->getHealth(), $owner->getMaxHealth()));
            }
        }
    }

}