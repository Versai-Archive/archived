<?php

namespace Versai\arenas\commands\subcommands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\arenas\Arenas;
use Versai\arenas\Constants;
use Versai\arenas\libs\CortexPE\Commando\args\RawStringArgument;
use Versai\arenas\libs\CortexPE\Commando\BaseSubCommand;
use Versai\arenas\libs\CortexPE\Commando\exception\ArgumentOrderException;

class RemoveSubCommand extends BaseSubCommand implements Constants{

    private Arenas $plugin;

    public function __construct(Arenas $plugin, string $name, string $description = "", array $aliases = []){
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
        $this->usageMessage = "remove <arena>";
    }

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void{
        $this->setPermission(self::REMOVE_PERMISSION);
        $this->registerArgument(0, new RawStringArgument("arena", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($sender instanceof Player){
            if(isset($args["arena"])){
                if(!isset($this->plugin->arenas[$args["arena"]])){
                    $sender->sendMessage(TextFormat::RED . "You did not provide a registered arena name!");
                }else{
                    if(file_exists($this->plugin->path . $args["arena"] . ".json")){
                        unlink($this->plugin->path . $args["arena"] . ".json");
                        unset($this->plugin->arenas[$args["arena"]]);
                        $sender->sendMessage(TextFormat::GREEN . "Successfully removed arena: {$args["arena"]}!");
                        $this->plugin->loadArenas();
                    }
                }
            }else{
                $levelName = $sender->getWorld()->getDisplayName();
                if(!isset($this->plugin->arenas[$levelName])){
                    $sender->sendMessage(TextFormat::RED . "You are not currently in a registered arena!");
                }else{
                    if(file_exists($this->plugin->path . $levelName . ".json")){
                        unlink($this->plugin->path . $levelName . ".json");
                        unset($this->plugin->arenas[$levelName]);
                        $sender->sendMessage(TextFormat::GREEN . "Successfully removed arena: $levelName!");
                        $this->plugin->loadArenas();
                    }
                }
            }
        }
    }
}