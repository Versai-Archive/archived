<?php


namespace Versai\Sumo\Command\SubCommands;


use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Command\BaseSubCommand;
use Versai\Sumo\Command\SumoTournamentCommand;
use Versai\Sumo\Session\Session;
use Versai\Sumo\Sumo;

class CloseSubCommand extends BaseSubCommand
{
    public function __construct(Sumo $sumo, SumoTournamentCommand $command)
    {
        parent::__construct($sumo, $command, "close", "Close your current sumo session");
        $this->setPermission("sumo.commands.start");
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            return;
        }

        if (empty($this->getPlugin()->currentSessions[$sender->getName()])) {
            $sender->sendMessage(TextFormat::RED . "You are currently not hosting a sumo tournament!");
            return;
        }

        $session = $this->getPlugin()->currentSessions[$sender->getName()];

        if ($session->currentState === Session::GAME_STATE_ONGOING) {
            $sender->sendMessage(TextFormat::RED . "The sumo tournament is already on-going!");
            return;
        }

        if ($this->getPlugin()->closeSession($sender)) {
            $sender->sendMessage(TextFormat::GREEN . "Successfully closed your current sumo tournament session");
        } else {
            $sender->sendMessage(TextFormat::RED . "You are currently not hosting a sumo tournament!");
        }
    }
}