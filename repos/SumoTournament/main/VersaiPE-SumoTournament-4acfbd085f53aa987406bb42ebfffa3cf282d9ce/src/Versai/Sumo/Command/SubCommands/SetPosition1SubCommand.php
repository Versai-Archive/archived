<?php


namespace Versai\Sumo\Command\SubCommands;


use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Command\BaseSubCommand;
use Versai\Sumo\Command\SumoTournamentCommand;
use Versai\Sumo\Sumo;

class SetPosition1SubCommand extends BaseSubCommand
{
    public function __construct(Sumo $sumo, SumoTournamentCommand $command)
    {
        parent::__construct($sumo, $command, "setpos1", "Select the current position where player 1 should join");
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
            $arena->position2 = $sender->getPosition()->asVector3();
            $arena->yaw2 = $sender->getYaw();
            $sender->sendMessage($this->getPlugin()->getMessageConfig()->get("set-position-1"));
        } else {
            $sender->sendMessage(TextFormat::RED . "Yikes! Seems like you didn't start an arena. Do /sumo create <level>");
        }
    }
}