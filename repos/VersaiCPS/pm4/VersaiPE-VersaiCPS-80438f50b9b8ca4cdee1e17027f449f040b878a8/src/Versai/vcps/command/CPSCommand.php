<?php

namespace Versai\vcps\command;

use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use Versai\vcps\data\DataManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CPSCommand extends Command implements PluginOwned {

    use PluginOwnedTrait;

    public function __construct(string $name = "cps", string $description = "", string $usageMessage = null, array $aliases = []){
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setDescription('Allows to get click data of the specified player');
        $this->setUsage(TextFormat::RED . '/cps <player>');
        $this->setPermission('vcps.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
        if($this->testPermission($sender)){
            $selectedPlayer = $args[0] ?? null;
            if(isset($args[1])){
                $selectedPlayer = implode(' ', $args);
            }
            if($selectedPlayer === null){
                $sender->sendMessage($this->getUsage());
            } else {
                $player = Server::getInstance()->getPlayerByPrefix($selectedPlayer);
                if($player === null){
                    $sender->sendMessage(TextFormat::RED . 'Player was not found.');
                } else {
                    $data = DataManager::getInstance()->get($player);
                    if($data === null){
                        $sender->sendMessage(TextFormat::RED . 'Something went wrong - contact a developer about this.');
                    } else {
                        $currentCPS = $data->currentCPS;
                        $averageCPS = round(array_sum($data->cpsList) / count($data->cpsList), 3);
                        $sender->sendMessage(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "{$player->getName()}'s CPS Info:\n" . TextFormat::RESET . TextFormat::BLUE . "Current CPS: " . TextFormat::AQUA . $currentCPS . PHP_EOL . TextFormat::BLUE . "Average CPS: " . TextFormat::AQUA . $averageCPS);
                    }
                }
            }
        }
    }

}