<?php


namespace Martin\RedRover\Command\SubCommand;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\GameAPI\Types\GameStateType;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class KickCommand extends BaseGameSubCommand
{

    public function onRun(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.player-only"));
            return;
        }

        $game = $this->getPlugin()->getGameByPlayer($sender);
        if (is_null($game)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.in-game"));
            return;
        }

        if ($game->getCreator() !== $sender) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.close.creator-only"));
            return;
        }

        if ($game->getCurrentState() !== GameStateType::STATE_WAITING) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.kick.game-started"));
            return;
        }

        if (empty($args[0])) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.argument-missing", [
                "position" => 1,
                "type" => "PLAYER"
            ]));

            return;
        }


        $player = $this->getPlugin()->getServer()->getPlayer($args[0]);
        if (!$player) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.player-not-found"));
            return;
        }

        if ($game->removePlayer($player)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.kick.kicked", ["player" => $player->getName()]));
        } else {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.kick.not-in-event"));
        }
    }

    protected function prepare(): void
    {
        $this->setPermission("redrover.games");
    }
}