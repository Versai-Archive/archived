<?php


namespace Martin\Sumo\Command\SubCommands;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\Sumo\Form\JoinForm;
use Martin\Sumo\Game\Sumo;
use Martin\Sumo\Main;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SpectateCommand extends BaseGameSubCommand
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
            $sender->sendMessage(Main::PREFIX . TextFormat::RED . "This player was not found!");
            return;
        }

        if (!($game = $this->getPlugin()->getGameByPlayer($targetPlayer)) || !($game instanceof Sumo)) {
            $sender->sendMessage(Main::PREFIX . TextFormat::RED . "This player is not inside an sumo tournament!");
            return;
        }

        $game->addSpectator($sender);
        $sender->sendMessage(Main::PREFIX . TextFormat::GRAY . "You successfully joined the game of {$game->getCreator()} as a spectator");
    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }
}