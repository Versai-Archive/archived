<?php

namespace Martin\Sumo\Command\SubCommands;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\GameAPI\Types\GameStateType;
use Martin\Sumo\Form\JoinForm;
use Martin\Sumo\Game\Sumo;
use Martin\Sumo\Main;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class JoinCommand extends BaseGameSubCommand
{

    public function onRun(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if ($this->getPlugin()->inGame($sender)) {
            $sender->sendMessage(Main::PREFIX . TextFormat::RED . "You are currently inside a game! You may leave using /sumo leave");
            return;
        }

        if (empty($args[0])) {
            $sender->sendForm(new JoinForm($this->getPlugin()));
            return;
        }

        $targetName = $args[0];
        $targetPlayer = Server::getInstance()->getPlayer((string)$targetName);

        if (!$targetPlayer) {
            if (!($game = $this->getPlugin()->getGameByCode($args[0])) || !($game instanceof Sumo)) {
                $sender->sendMessage(Main::PREFIX . TextFormat::RED . "A game with this player or code was not found!");
                return;
            }

            if ($game->getCurrentState() === GameStateType::STATE_ONGOING) {
                $sender->sendMessage(Main::PREFIX . TextFormat::RED . "The current event already started! You may join using /sumo spectate");
                return;
            }

            $sender->sendMessage(Main::PREFIX . TextFormat::GRAY . "You successfully joined the sumo event of " . $game->getCreator()->getName());
            $game->addPlayer($sender);
            $game->broadcast(Main::PREFIX . TextFormat::GRAY . "The player {$sender->getName()} joined the current event", [$sender->getLowerCaseName()]);
            return;
        }

        if (!($game = $this->getPlugin()->getGameByPlayer($targetPlayer)) || !($game instanceof Sumo)) {
            $sender->sendMessage(Main::PREFIX . TextFormat::RED . "This player is not inside an sumo tournament!");
            return;
        }

        if ($game->isPrivate()) {
            $sender->sendMessage(Main::PREFIX . TextFormat::RED . "This game is private and you need a code to join it");
            return;
        }

        if ($game->getCurrentState() === GameStateType::STATE_ONGOING) {
            $sender->sendMessage(Main::PREFIX . TextFormat::RED . "The current event already started! You may join using /sumo spectate");
            return;
        }

        $sender->sendMessage(Main::PREFIX . TextFormat::GRAY . "You successfully joined the sumo event of " . $game->getCreator()->getName());
        $game->addPlayer($sender);
        $game->broadcast(Main::PREFIX . TextFormat::GRAY . "The player {$sender->getName()} joined the current event", [$sender->getLowerCaseName()]);
    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }
}