<?php

namespace sexysoup;

use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onInteract(PlayerItemUseEvent $event){

        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->getId() == 282) {
            if($player->getHealth() == 20){
                //$player->sendMessage("You can't eat that rn sorry bro..");
            } else {
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                $player->setHealth($player->getHealth() + 8);
            }
        }
    }
}
