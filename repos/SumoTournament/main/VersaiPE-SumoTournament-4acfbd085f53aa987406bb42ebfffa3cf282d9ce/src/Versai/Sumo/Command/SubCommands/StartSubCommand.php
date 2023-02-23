<?php


namespace Versai\Sumo\Command\SubCommands;


use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Command\BaseSubCommand;
use Versai\Sumo\Command\SumoTournamentCommand;
use Versai\Sumo\Sumo;

class StartSubCommand extends BaseSubCommand
{
    public function __construct(Sumo $sumo, SumoTournamentCommand $command)
    {
        parent::__construct($sumo, $command, "start", "Start a sumo tournament");
        $this->setPermission("sumo.commands.start");
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            return;
        }

        if (isset($this->getPlugin()->currentSessions[$sender->getName()])) {
            $sender->sendMessage(TextFormat::RED . "You are already hosting a sumo tournament! Close it with /sumo close");
            return;
        }


    }
}