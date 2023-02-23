<?php


namespace Martin\Sumo\Command\SubCommands;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\GameAPI\Types\GameStateType;
use Martin\Sumo\Game\Sumo;
use Martin\Sumo\Main;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class CloseCommand extends BaseGameSubCommand
{

    public function onRun(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (($game = $this->getPlugin()->getGameByPlayer($sender)) === null) {
            $sender->sendMessage(Main::PREFIX . "§cYou've to be inside an sumo tournament to execute this command!");
            return;
        }

        if ($game->getCurrentState() !== GameStateType::STATE_WAITING) {
            $sender->sendMessage(Main::PREFIX . "§cThe sumo tournament already started!");
            return;
        }

        if ($game->getCreator() !== $sender) {
            $sender->sendMessage(Main::PREFIX . "§cYou've to be the owner of this sumo tournement to start it");
            return;
        }

        $game->endGame(Sumo::closingParameter());
    }

    protected function prepare(): void
    {
        $this->setPermission("sumo.games");
    }
}