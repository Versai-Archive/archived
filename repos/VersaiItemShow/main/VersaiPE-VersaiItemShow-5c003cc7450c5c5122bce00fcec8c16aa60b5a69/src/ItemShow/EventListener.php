<?php

namespace ItemShow;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat as C;

class EventListener implements Listener{

    private $plugin;

    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }

    public function onPlayerChat(PlayerChatEvent $ev): void{

        $player = $ev->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $msg = $ev->getFormat();

        $format = C::YELLOW . "x{amount}" . " " . C::AQUA . "{name}" . " " . C::YELLOW . "«";

        if($player->hasPermission("item.chat")){
            $format = str_replace("{name}", $item->getName(), $format);
            $format = str_replace("{amount}", $item->getCount(), $format);

            $msg = str_replace("[item]", $format, $msg);
        }
//I hate myself this took wayyyyyy to long to figure out -_-
        $ev->setFormat($msg);
        $this->plugin->getServer()->broadcastMessage(str_replace("[item]", $ev->getMessage(), $ev->getFormat()), $ev->getRecipients());
        $ev->setCancelled();
    }
}