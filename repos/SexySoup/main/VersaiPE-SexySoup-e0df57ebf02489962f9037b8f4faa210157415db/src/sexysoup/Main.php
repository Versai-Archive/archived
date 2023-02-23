<?php

namespace sexysoup;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onInteract(PlayerInteractEvent $event){

        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->getId() == 282) {
            $event->setCancelled(true);
            $item->pop();
            $player->getInventory()->setItemInHand($item);
            $player->setHealth($player->getHealth() + 8);
            $player->setFood(20);
        }
    }
//no way this works
//dumbass why would u put it random
}
