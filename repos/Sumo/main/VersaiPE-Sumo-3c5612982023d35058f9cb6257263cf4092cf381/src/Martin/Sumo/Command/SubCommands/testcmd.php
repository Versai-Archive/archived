<?php


namespace Martin\Sumo\Command\SubCommands;


use Martin\GameAPI\Command\BaseGameSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;

class testcmd extends BaseGameSubCommand
{

    public function onRun(CommandSender $sender, array $args): void
    {
        $commander = new ConsoleCommandSender();

        if (isset($args[0])) {
            Server::getInstance()->getCommandMap()->dispatch($commander, "s s tester1");
            Server::getInstance()->getCommandMap()->dispatch($commander, "s c tester1 /sumo create SkyBlock");
            return;
        }

        $p = "lol";
        $p2 = "lol1";
        $p1 = "lol3";

        $commands = [
            "s s $p",
            "s s $p2",
            "s s $p1",
            "s c $p /sumo join ex",
            "s c $p2 /sumo join ex",
            "s c $p1 /sumo join ex",
        ];

        foreach ($commands as $command) {
            Server::getInstance()->getCommandMap()->dispatch($commander, $command);
        }
    }

    # specter ........... testing so i dont have to tap all

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }
}