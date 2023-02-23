<?php


namespace Versai\Sumo\Command\SubCommands;


use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Command\BaseSubCommand;
use Versai\Sumo\Command\SumoTournamentCommand;
use Versai\Sumo\Session\BuildingArena;
use Versai\Sumo\Sumo;

class CreateSubCommand extends BaseSubCommand
{
    public function __construct(Sumo $sumo, SumoTournamentCommand $command)
    {
        parent::__construct($sumo, $command, "create", "Initialize a sumo tournament arena");
        $this->setPermission("sumo.commands.create");
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED . "This is a player-only command!");
            return;
        }

        if (empty($args[0])) {
            $sender->sendMessage(TextFormat::RED . "You need to provide a name for the arena!");
            return;
        }

        $name = $args[0];

        if (BuildingArena::validateName($this->getPlugin(), $name)) {
            $arena = new BuildingArena();
            $arena->name = $name;
            $arena->level = $sender->getLevel();
            $this->getPlugin()->buildArenas[$sender->getName()] = $arena;
        } else {
            $sender->sendMessage(TextFormat::RED . "Yikes! An arena what that name already exists.");
        }

    }
}