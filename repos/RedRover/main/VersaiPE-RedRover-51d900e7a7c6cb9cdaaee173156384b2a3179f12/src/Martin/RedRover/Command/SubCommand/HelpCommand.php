<?php


namespace Martin\RedRover\Command\SubCommand;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\RedRover\Command\RedRoverCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;

class HelpCommand extends BaseGameSubCommand
{
    public function onRun(CommandSender $sender, array $args): void
    {
        $base = $this->getPlugin()->getCommand("redrover");
        $sender->sendMessage($this->getPlugin()->getMessage("commands.help.title"));

        if ($base instanceof RedRoverCommand) {
            foreach ($base->getSubCommands() as $subCommand) {
                if ($subCommand->testPermissionSilent($sender)) {
                    $sender->sendMessage($this->getPlugin()->getMessage("commands.help.title", ["command" => $subCommand->getName(), "description" => $subCommand->getDescription()]));
                }
            }
        } else {
            throw new CommandException("Class HelpCommand: RedRoverCommand not found");
        }
    }

    protected function prepare(): void
    {
    }
}