<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Form;

use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Commands\ModerationCommand;
use Versai\ModerationPM\Database\Container\PlayerData;
use function array_values;
use function strtolower;

abstract class FormModerationCommand extends ModerationCommand{

    /**
     * Splits the command into the run as player and run as console
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    final public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(isset($args['player'])){
            $name = $args['player'];
            $player = $this->resolveOnlinePlayer($sender, $args['player'], true);
            if ($player !== null){
                $name = $player->getName();
            }
            $data = $this->plugin->getPlayerData()->get($name);
            if ($data !== null){
                $name = $data->getName();
            }
            $this->passPlayerData($name, null, null, true, function (?array $playerDataArray) use ($sender, $name, $args): void {
                if ($playerDataArray !== null){
                    $lowerCaseName = strtolower($name);
                    /** @var PlayerData $playerData */
                    $playerData = array_values($playerDataArray)[0];
                    if (strtolower($playerData->getName()) === $lowerCaseName){
                        if ($sender instanceof ConsoleCommandSender || (isset($args['length']) && isset($args['reason']) && $args['reason'] !== '' && ($good = true))){
                            if (isset($good) || (isset($args['length']) && isset($args['reason']))){
                                $this->runAsConsole($sender, $playerDataArray, $args);
                            }else{
                                $this->sendUsage();
                            }
                        } elseif ($sender instanceof Player){
                            $this->runAsPlayer($sender, $playerDataArray, $args);
                        }else{
                            $this->sendUsage();
                        }
                    }
                }else{
                    $sender->sendMessage(TextFormat::RED . 'Player does not exist!');
                }
            });
        }else{
            $this->sendUsage();
        }
    }

    /**
     * @param Player $sender
     * @param array $data
     * @param array $args
     */
    abstract public function runAsPlayer(Player $sender, array $data, array $args): void;

    /**
     * @param CommandSender $sender
     * @param array $data
     * @param array $args
     */
    abstract public function runAsConsole(CommandSender $sender, array $data, array $args): void;
}
