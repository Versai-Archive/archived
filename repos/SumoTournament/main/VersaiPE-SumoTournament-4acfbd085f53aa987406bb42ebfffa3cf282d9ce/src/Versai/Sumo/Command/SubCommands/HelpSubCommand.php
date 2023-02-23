<?php


namespace Versai\Sumo\Command\SubCommands;


use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Command\BaseSubCommand;
use Versai\Sumo\Command\SumoTournamentCommand;
use Versai\Sumo\Sumo;

class HelpSubCommand extends BaseSubCommand
{
    public function __construct(Sumo $sumo, SumoTournamentCommand $command)
    {
        parent::__construct($sumo, $command, "help", "Get help about the sumo tournament", null, ["commands"]);
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $sender->sendMessage(TextFormat::AQUA . "Sumo Help");

        $commands = array_filter($this->getBase()->getSubCommands(), function ($subCommand) use ($sender) {
            return $subCommand->testPermissionSilent($sender);
        });

        foreach ($commands as $command) {
            $sender->sendMessage(TextFormat::AQUA . "/sumo " . $command->getName() . " - " . $command->getDescription());
        }
    }
}