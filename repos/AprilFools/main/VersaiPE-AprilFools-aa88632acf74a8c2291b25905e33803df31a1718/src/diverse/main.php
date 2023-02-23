<?php

namespace diverse;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class main extends PluginBase implements Listener {

    private $randomizedScale;

   public function onEnable() {
      $this->getServer()->getPluginManager()->registerEvents($this, $this);
      $this->randomizedScale = $this->rand_float(0.65, 1.45);
      $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(int $currentTick) : void{
          $players = $this->getServer()->getOnlinePlayers();
          // every 90 seconds
          if(($currentTick - 1) % 1800 === 0){
              $this->randomizedScale = $this->rand_float(0.65, 1.45, $this->randomizedScale);
              foreach($players as $onlinePlayer){
                  $onlinePlayer->setScale($this->randomizedScale);
              }
          }
          $skins = [];
          foreach($players as $player){
              $skins[] = $player->getSkin();
          }
          foreach($players as $player){
              $key = array_rand($skins);
              $player->setSkin($skins[$key]);
              // every player should have a different skin
              unset($skins[$key]);
          }
          unset($skins);
      }), 600);
   }

   public function onQuit(PlayerQuitEvent $event) {
      $player = $event->getPlayer();
      $name = $player->getName();
      // instead of Event->setQuitMessage() since there might be some other plugin setting it ([-] Player)
      $this->getServer()->broadcastMessage(TextFormat::GREEN . $name . " has been permanently banned for leaving the server. Leaving the server is NOT allowed.");
   }

   public function onChat(PlayerChatEvent $event){
       if(in_array(strtolower($event->getMessage()), ["gg", "gf", "good game"])){
           $event->getPlayer()->sendMessage(TextFormat::RED . "Please refrain from being toxic in the chat.");
       } elseif(in_array(strtolower($event->getMessage()), ["l", "ez", "bad", "best ww", "garbage", "too ez"])){
           $event->getPlayer()->sendMessage(TextFormat::GREEN . "Thank you for not being toxic in the chat!");
       }
   }

   public function onJoin(PlayerJoinEvent $event){
       $event->getPlayer()->setScale($this->randomizedScale);
       $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick) use($event) : void{
           $msg = TextFormat::RED .
               <<<TEST
           IMPORTANT NOTICE:
           
           Sue has transferred the server to Ethaniccc. Sue no longer owns the server and Ethaniccc is now the new owner. If you
           have any questions, please contact ethaniccc on Discord (@ethaniccc#1659)
           TEST;
           $event->getPlayer()->sendMessage($msg);
       }), 20);
   }

   private function rand_float($min=0, $max=1, $base = 1, $mul=1000000000000){
       if ($min > $max){
           return false;
       }
       $rand = mt_rand($min * $mul,$max * $mul) / $mul;
       return abs($base - 1) < 0.15 ? $rand : $this->rand_float($min, $max);
   }



}