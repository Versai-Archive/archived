<?php


namespace Martin\RedRover\Command\SubCommand;


use Martin\GameAPI\Command\BaseGameSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class StartCommand extends BaseGameSubCommand
{
    public function onRun(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.player-only"));
            return;
        }

        $game = $this->getPlugin()->getGameByPlayer($sender);
        if ($game === null) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.in-game"));
            return;
        }

        if ($game->getCreator() !== $sender) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.close.creator-only"));
            return;
        }

        $game->startGame();
    }

    protected function prepare(): void
    {
        $this->setPermission("redrover.games");
    }
}