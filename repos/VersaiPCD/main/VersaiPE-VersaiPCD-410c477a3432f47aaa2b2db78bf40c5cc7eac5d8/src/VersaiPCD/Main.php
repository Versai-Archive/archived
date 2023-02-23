<?php

namespace VersaiPCD;

use pocketmine\item\EnderPearl;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;

class Main extends PluginBase implements Listener{

    private $config;
    private $versaipcd;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");
		$this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
    }

    public function onEnderPearl(PlayerInteractEvent $event){
        $item = $event->getItem();
        if($item instanceof EnderPearl) {
            $cooldown = $this->config->get("cooldown"); //duo if i did deprecated method i sorry :)
            $player = $event->getPlayer();
            if (isset($this->versaipcd[$player->getName()]) and time() - $this->versaipcd[$player->getName()] < $cooldown) {
                $event->setCancelled(true);
                $time = time() - $this->versaipcd[$player->getName()];
                $message = $this->config->get("message");
                $message = str_replace("{cooldown}", ($cooldown - $time), $message);
                $player->sendMessage($message);
            } else {
                $this->versaipcd[$player->getName()] = time();
            }
        }
    }
}
//i hope this works :)