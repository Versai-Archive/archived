<?php

namespace Versai\arenas\commands\subcommands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Versai\arenas\Arenas;
use Versai\arenas\Constants;
use Versai\arenas\libs\CortexPE\Commando\BaseSubCommand;

class ListSubCommand extends BaseSubCommand implements Constants{

    private Arenas $plugin;

    public function __construct(Arenas $plugin, string $name, string $description = "", array $aliases = []){
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    public function prepare(): void{
        $this->setPermission(self::LIST_PERMISSION);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($sender instanceof Player){
            $str = "";
            if (is_array($this->plugin->arenas)){
                $count = count($this->plugin->arenas);
                foreach (array_values($this->plugin->arenas) as $key => $arena){
                    if ($key === $count - 1){
                        $str .= "and {$arena->getName()}!";
                    } else{
                        $str .= "{$arena->getName()}, ";
                    }
                }
            }else{
                $sender->sendMessage("§cNo arenas have been setup!");
            }
            $sender->sendMessage("§9List of loaded arenas: $str");
        }
    }
}