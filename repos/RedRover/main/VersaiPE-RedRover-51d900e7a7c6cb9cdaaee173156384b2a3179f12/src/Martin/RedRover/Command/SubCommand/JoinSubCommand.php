<?php


namespace Martin\RedRover\Command\SubCommand;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\RedRover\Form\JoinForm;
use Martin\RedRover\Game\RedRover;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class JoinSubCommand extends BaseGameSubCommand
{

    public function onRun(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.player-only"));
            return;
        }

        if ($this->getPlugin()->inGame($sender)) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.already-in-game"));
            return;
        }

        if (empty($args[0])) {
            $sender->sendForm(new JoinForm());
            return;
        }

        $argument = $args[0];
        $player = $this->getPlugin()->getServer()->getPlayer($argument);

        if ($player === null) {
            $sender->sendMessage($this->getPlugin()->getMessage("commands.error.player-not-found"));
            return;
        }


        /** @var RedRover $game */
        if (($game = $this->getPlugin()->getGameByPlayer($player)) instanceof RedRover) {
            $team = $game->getTeamWithLessPlayers();

            if (empty($args[1])) {
                $team = $game->getTeamWithLessPlayers();

                if ($team) {
                    $game->toTeam($player, $team->getIdentifier());
                }
            } else if (($t = strtolower($args[1])) === "red") {
                $team = $game->getRedTeam();
            } else if ($t === "blue") {
                $team = $game->getBlueTeam();
            } else if ($t === "spec" || $t === "spectator") {
                $sender->sendMessage($this->getPlugin()->getPrefix() . "§cPlease use /redrover spectate to join as a spectator");
            } else {
                $sender->sendMessage($this->getPlugin()->getPrefix() . "§cTeam not found! Teams are Red and Blue");
            }

            if ($team->isFull()) {
                $sender->sendMessage($this->getPlugin()->getMessage("commands.error.team-full", ["team" => $team->toString()]));
                return;
            }

            $game->toTeam($sender, $team->getIdentifier());
            $game->broadcast($this->getPlugin()->getMessage("broadcasts.game.player-join", ["player" => $sender->getName(), "team" => $team->toString()]));
        }

    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }
}