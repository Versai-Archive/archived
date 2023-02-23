<?php


namespace Martin\RedRover\Command\SubCommand;


use Martin\GameAPI\Command\BaseGameSubCommand;
use Martin\RedRover\Form\TeamForm;
use Martin\RedRover\Game\RedRover;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class TeamCommand extends BaseGameSubCommand
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

        if (empty($args[0])) {
            TeamForm::createForm($sender);
            return;
        }

        $t = strtolower($args[0]);


        if ($t === "red") {
            $team = $game->getRedTeam();
            $game->toTeam($sender, RedRover::TEAM_RED);
        } else if ($t === "blue") {
            $team = $game->getBlueTeam();
            $game->toTeam($sender, RedRover::TEAM_BLUE);
        } else if ($t === "spectator" || $t === "spec") {
            $game->addSpectator($sender);
            return;
        } else {
            $sender->sendMessage("Available teams are: Red, Blue, Spectator!");
            return;
        }

        $game->broadcast($this->getPlugin()->getMessage("broadcasts.game.player-join", ["team" => $team->toString(), "player" => $sender->getName()]));
    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }
}