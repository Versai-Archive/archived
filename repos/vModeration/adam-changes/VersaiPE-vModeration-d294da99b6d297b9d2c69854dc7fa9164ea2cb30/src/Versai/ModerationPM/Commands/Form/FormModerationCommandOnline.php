<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Form;

use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use Versai\ModerationPM\Commands\ModerationCommand;

abstract class FormModerationCommandOnline extends ModerationCommand{

    /**
     * Splits the command into the run as player and run as console
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    final public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(isset($args['player'])){
            if (($player = $this->resolveOnlinePlayer($sender, $args['player'])) && $player !== null){
                if ($sender instanceof Player){
                    $this->runAsPlayer($sender, $player, $args);
                } elseif ($sender instanceof ConsoleCommandSender){
                    $this->runAsConsole($sender, $player, $args);
                }
            }
        }else{
            $this->sendUsage();
        }
    }

    /**
     * @param Player $sender
     * @param Player $player
     * @param array $args
     */
    abstract public function runAsPlayer(Player $sender, Player $player, array $args): void;

    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param array $args
     */
    abstract public function runAsConsole(CommandSender $sender, Player $player, array $args): void;
}
