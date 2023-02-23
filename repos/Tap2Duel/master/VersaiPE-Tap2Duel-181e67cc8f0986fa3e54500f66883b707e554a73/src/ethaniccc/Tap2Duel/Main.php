<?php

declare(strict_types=1);

namespace ethaniccc\Tap2Duel;

use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;

class Main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @ignoreCancelled false
     */
    public function onHit(EntityDamageByEntityEvent $event) : void{
        try{
            if(!$event instanceof EntityDamageByChildEntityEvent){
                $damager = $event->getDamager(); $damaged = $event->getEntity();
                if($damager instanceof Player && $damaged instanceof Player && $damager->getInventory()->getItemInHand()->getId() === ItemIds::DIAMOND_SWORD && $damager->getLevelNonNull()->getFolderName() === 'hub'){
                    $name = $damaged->getName();
                    $this->getServer()->dispatchCommand($damager, 'duel ' . $name);
                }
            }
        } catch(AssumptionFailedError $e){}
    }

}
