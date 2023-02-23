<?php

namespace Versai\arenas\commands\subcommands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\arenas\Arena;
use Versai\arenas\Arenas;
use Versai\arenas\Constants;
use Versai\arenas\libs\CortexPE\Commando\args\RawStringArgument;
use Versai\arenas\libs\CortexPE\Commando\BaseSubCommand;
use Versai\arenas\libs\CortexPE\Commando\exception\ArgumentOrderException;

class InfoSubCommand extends BaseSubCommand implements Constants{

    private Arenas $plugin;

    public function __construct(Arenas $plugin, string $name, string $description = "", array $aliases = []){
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
        $this->usageMessage = "info <arena>";
    }

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void{
        $this->setPermission(self::INFO_PERMISSION);
        $this->registerArgument(0, new RawStringArgument("arena", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($sender instanceof Player){
            if(isset($args["arena"])){
                if(isset($this->plugin->arenas[$args["arena"]])){
                    $arena = $this->plugin->arenas[$args["arena"]];
                    $this->sendInfo($sender, $arena);
                }else{
                    $sender->sendMessage(TextFormat::RED . "That arena does not exist!");
                }
            }else{
                $levelName = $sender->getWorld()->getDisplayName();
                if(isset($this->plugin->arenas[$levelName])){
                    $arena = $this->plugin->arenas[$levelName];
                    $this->sendInfo($sender, $arena);
                }else{
                    $sender->sendMessage($this->getUsageMessage());
                }
            }
        }
    }

    public function sendInfo(Player $player, Arena $arena){
        $player->sendMessage(TextFormat::BLUE . "Info for arena: {$arena->getName()}");
        foreach($arena->getAllInfo() as $info){
            $player->sendMessage(TextFormat::BLUE . "$info");
        }
    }

    /**
     * @param bool $bool
     * @return string
     */
    public function boolToString(bool $bool): string{
        return $bool ? "true" : "false";
    }
}