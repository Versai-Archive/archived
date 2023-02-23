<?php


namespace Versai\Sumo\Command\SubCommands;


use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Command\BaseSubCommand;
use Versai\Sumo\Command\SumoTournamentCommand;
use Versai\Sumo\Sumo;

class SetSpawnSubCommand extends BaseSubCommand
{
    public function __construct(Sumo $sumo, SumoTournamentCommand $command)
    {
        parent::__construct($sumo, $command, "setspawn", "Select the current position where the spectator should join", null);
        $this->setPermission("sumo.creation");
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED . "This is a player-only command!");
            return;
        }

        if ($this->getPlugin()->isBuildingArena($sender)) {
            $arena = $this->getPlugin()->buildArenas[$sender->getName()];
            $arena->spawningPosition = $sender->getPosition()->asVector3();
            $arena->spawningYaw = $sender->getYaw();
            $sender->sendMessage(TextFormat::GREEN . "Successfully added the spawn position");
        } else {
            $sender->sendMessage(TextFormat::RED . "Yikes! Seems like you didn't start an arena. Do /sumo create <level>");
        }
    }
}