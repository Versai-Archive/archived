<?php


namespace Martin\RedRover\Command\SubCommand;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\RedRover\Form\SpectateForm;
use Martin\RedRover\Game\RedRover;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class SpectateCommand extends BaseGameSubCommand
{

    public function onRun(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.player-only"));
            return;
        }

        if (empty($args[0])) {
            $sender->sendForm(new SpectateForm());
            return;
        }

        $player = $this->getPlugin()->getServer()->getPlayer($args[0]);

        if ($player === null) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.player-not-found"));
            return;
        }

        /** @var RedRover $game */
        if (($game = $this->getPlugin()->getGameByPlayer($player)) instanceof RedRover) {
            $game->addSpectator($sender);
            $sender->sendMessage($this->getPlugin()->getMessage("commands.spectate.joined", ["player" => $game->getCreator()->getName()]));
        }
    }

    protected function prepare(): void
    {
        $this->setAliases(["spec"]);
    }
}