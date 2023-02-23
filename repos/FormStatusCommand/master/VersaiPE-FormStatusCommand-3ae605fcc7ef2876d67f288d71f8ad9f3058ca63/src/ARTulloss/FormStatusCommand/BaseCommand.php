<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/9/2020
 * Time: 5:06 PM
 */
declare(strict_types=1);

namespace ARTulloss\FormStatusCommand;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

abstract class BaseCommand extends PluginCommand{
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if(!$this->testPermission($sender))
            return false;

        if ($sender instanceof ConsoleCommandSender)
            $this->handleConsole($sender, $commandLabel, $args);
        elseif($sender instanceof Player)
            $this->parseCommand($sender, $args);

        return true;
    }
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    abstract protected function handleConsole(CommandSender $sender, string $commandLabel, array $args): void;
    /**
     * @param Player $sender
     * @param array $args
     */
    abstract protected function parseCommand(Player $sender, array $args): void;
    /**
     * @param Player $sender
     * @param string $name
     */
    abstract protected function sendForm(Player $sender, string $name = \null): void;
}