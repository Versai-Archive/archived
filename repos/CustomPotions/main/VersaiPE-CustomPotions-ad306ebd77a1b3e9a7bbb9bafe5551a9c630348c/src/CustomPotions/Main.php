<?php

namespace CustomPotions;

use CustomPotions\CPListener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;


class Main extends PluginBase implements Listener {

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new CPListener($this), $this);
    }

    public function onDisable(){

    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool{

        switch($cmd->getName()){
            case "custompotion":
                if(!$sender->hasPermission("custompotions")) {
                    $sender->sendMessage("§cYou dont have permission to use this command!");
                    break;
                }

                if(isset($args[0])){
                    if(!$player = $sender->getServer()->getPlayer($args[0])){
                        $sender->sendMessage("§l§8(§c!§8)§r §7That player does not exist!§r");
                        break;
                }

                $name = $player->getName();
                
                if(isset($args[1])){

                switch($args[1]){

                    case "raiding":
                        $sender->sendMessage("§l§8(§a!§8)§r §7You have given " . $name . " a §l§cRaiding Elixir§r§7.§r");
                        $raiding = Item::get(Item::POTION, 100, 1);
                        $raiding->setCustomName("§l§cRaiding Elixir§r");
                        $raiding->setLore([

                            "\n§8* §aSpeed I §7(6:00)\n§8* §aHaste II §7(6:00)\n§8* §aNight Vision §7(3:00)§r"

                        ]);
                        $player->getInventory()->addItem($raiding);
                        break;

                    case "pvp":
                        $sender->sendMessage("§l§8(§a!§8)§r §7You have given " . $name . " a §l§bPvP Elixir§r§7.§r");
                        $pvp = Item::get(Item::POTION, 101, 1);
                        $pvp->setCustomName("§l§bPvP Elixir§r");
                        $pvp->setLore([

                            "\n§8* §aJump Boost I §7(3:00)\n§8* §aStrength I §7(0:30)\n§8* §aNight Vision §7(6:00)\n§8* §aFire Resistance §7(6:00)§r"

                        ]);

                        $player->getInventory()->addItem($pvp);
                        break;

                   case "healer":
                       $sender->sendMessage("§l§8(§a!§8)§r §7You have given " . $name . " a §l§aHealer Elixir§r§7.§r");
                       $healer = Item::get(Item::POTION, 102, 1);
                       $healer->setCustomName("§l§eHealer Elixir§r");
                       $healer->setLore([

                           "\n§8* §aRegeneration II §7(3:00)\n§8* §aAbsorption II §7(3:00)§r"

                       ]);
                       $player->getInventory()->addItem($healer);
                       break;

                   case "mining":
                       $sender->sendMessage("§l§8(§a!§8)§r §7You have given " . $name . " a §l§dMining Elixir§r§7.§r");
                       $miner = Item::get(Item::POTION, 103, 1);
                       $miner->setCustomName("§l§dMining Elixir§r");
                       $miner->setLore([

                           "\n§8* §aSpeed III §7(6:00)\n§8* §aHaste III §7(6:00)\n§8* §aFire Resistance II §7(7:00)\n§8* §aWater Breathing II §7(7:00)\n§8* §aNight Vision II §7(7:00)§r"

                       ]);
                       $player->getInventory()->addItem($miner);
                       break;

                    default:
                        $types = ["§l§craiding", "§l§bpvp", "§l§ahealer", "§l§dmining"];
                        $sender->sendMessage("§l§8(§c!§8)§r §7Unknown type: $args[1]");
                        $sender->sendMessage("§l§8(§c!§8)§r §7Available types:§r"." ".implode("§r, ", $types));

                    }

                } else {
                    $sender->sendMessage("Usage: /custompotion <player> <type>");
                }

                } else {
                    $sender->sendMessage("Usage: /custompotion <player> <type>");
                }
                
        }
        return true;
    }
}