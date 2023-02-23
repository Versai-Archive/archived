<?php
declare(strict_types=1);

namespace ARTulloss\FormStatusCommand;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;

abstract class BaseCommand extends Command {

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if(!$this->testPermission($sender)) {
            return false;
        }

        if ($sender instanceof ConsoleCommandSender) {
            $this->handleConsole($sender, $commandLabel, $args);
        }elseif($sender instanceof Player) {
            $this->parseCommand($sender, $args);
        }
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
     * @param string|null $name
     */
    abstract protected function sendForm(Player $sender, string $name = null): void;
}