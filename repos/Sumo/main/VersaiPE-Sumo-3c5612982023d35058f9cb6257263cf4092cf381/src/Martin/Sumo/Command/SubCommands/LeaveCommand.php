<?php


namespace Martin\Sumo\Command\SubCommands;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\Sumo\Main;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class LeaveCommand extends BaseGameSubCommand
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

        if ($game->getCreator() === $sender) {
            $sender->sendMessage(Main::PREFIX . TextFormat::RED . "If you want to close the game use /sumo close");
            return;
        }

        $sender->sendMessage(Main::PREFIX . TextFormat::GRAY . "Successfully left the current sumo tournament");

        $game->removePlayer($sender);
        $game->broadcast(Main::PREFIX . TextFormat::GRAY . $sender->getName() . " left the current sumo tournament");
    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }
}