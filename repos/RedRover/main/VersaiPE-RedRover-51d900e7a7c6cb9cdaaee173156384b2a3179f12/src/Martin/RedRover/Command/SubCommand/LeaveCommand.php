<?php


namespace Martin\RedRover\Command\SubCommand;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\GameAPI\Game\Team\Team;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class LeaveCommand extends BaseGameSubCommand
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

        $team = $game->getTeamByPlayer($sender);

        if ($team instanceof Team) {
            if ($team->removePlayer($sender)) {
                $sender->sendMessage($this->getPlugin()->getMessage("commands.leave.left", ["team" => $team->toString()]));
                # $game->broadcast($this->getPlugin()->getMessage("commands.leave.left-broadcast", ["team" => $team->toString(), "player" => $sender->getName()]));
            } else {
                $sender->sendMessage("lol what this shouldn't happen (LeaveCommand Team::removePlayer -> false)");
            }
        } else if (in_array($sender, $game->getSpectators(), true)) {
            $game->removeSpectator($sender);
            $sender->sendMessage($this->getPlugin()->getMessage("commands.leave.left", ["team" => "Spectator"]));
        }
    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }
}