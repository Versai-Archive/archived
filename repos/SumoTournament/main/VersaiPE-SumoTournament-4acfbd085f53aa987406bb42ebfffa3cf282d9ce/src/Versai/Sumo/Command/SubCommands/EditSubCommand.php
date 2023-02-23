<?php


namespace Versai\Sumo\Command\SubCommands;


use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Command\BaseSubCommand;
use Versai\Sumo\Command\SumoTournamentCommand;
use Versai\Sumo\Sumo;

/**
 * @todo
 * Class EditSubCommand
 * @package Versai\Sumo\Command\SubCommands
 */
class EditSubCommand extends BaseSubCommand
{
    public function __construct(Sumo $sumo, SumoTournamentCommand $command)
    {
        parent::__construct($sumo, $command, "edit", "Edit a sumo arena");
        $this->setPermission("sumo.creation");
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED . "This is a player-only command!");
            return;
        }

        if (empty($args[0])) {

            return;
        }
    }
}