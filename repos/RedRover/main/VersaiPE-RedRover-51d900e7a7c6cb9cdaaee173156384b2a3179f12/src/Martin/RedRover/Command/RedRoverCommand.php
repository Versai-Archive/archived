<?php


namespace Martin\RedRover\Command;


use Martin\GameAPI\Command\BaseGameCommand;
use Martin\RedRover\Command\SubCommand\CloseCommand;
use Martin\RedRover\Command\SubCommand\CreateCommand;
use Martin\RedRover\Command\SubCommand\CreatorCommand;
use Martin\RedRover\Command\SubCommand\HelpCommand;
use Martin\RedRover\Command\SubCommand\JoinSubCommand;
use Martin\RedRover\Command\SubCommand\KickCommand;
use Martin\RedRover\Command\SubCommand\LeaveCommand;
use Martin\RedRover\Command\SubCommand\PlayersCommand;
use Martin\RedRover\Command\SubCommand\SpectateCommand;
use Martin\RedRover\Command\SubCommand\StartCommand;
use Martin\RedRover\Command\SubCommand\TeamCommand;
use Martin\RedRover\Form\CreateForm;
use Martin\RedRover\Form\JoinForm;
use Martin\RedRover\Form\ManageForm;
use Martin\RedRover\Form\WaitingForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class RedRoverCommand extends BaseGameCommand
{
    protected function prepare(): void
    {
        $this->registerSubCommand(new CloseCommand($this->getPlugin(), "close", "Close/End the current RedRover event you are hosting"));
        $this->registerSubCommand(new CreateCommand($this->getPlugin(), "create", "Create a RedRover event"));
        $this->registerSubCommand(new CreatorCommand($this->getPlugin(), "creator", "Use a creator for maps"));
        $this->registerSubCommand(new HelpCommand($this->getPlugin(), "help", "Get a list of commands you can execute"));
        $this->registerSubCommand(new JoinSubCommand($this->getPlugin(), "join", "Join an RedRover event"));
        $this->registerSubCommand(new KickCommand($this->getPlugin(), "kick", "Kick someone out of the current RedRover event you are hosting"));
        $this->registerSubCommand(new LeaveCommand($this->getPlugin(), "leave", "Leave the current RedRover event you are in"));
        $this->registerSubCommand(new PlayersCommand($this->getPlugin(), "players", "Get a list of players inside the current RedRover event"));
        $this->registerSubCommand(new SpectateCommand($this->getPlugin(), "spectate", "Join an RedRover event as a spectator"));
        $this->registerSubCommand(new StartCommand($this->getPlugin(), "start", "Start the current RedRover event you are in"));
        $this->registerSubCommand(new TeamCommand($this->getPlugin(), "team", "Switch teams"));
    }

    protected function onRunEmptyArguments(CommandSender $sender, array $args): void
    {
        if (!($sender instanceof Player)) {
            $this->getSubCommands()["help"]->execute($sender, "0-args-help", $args);
            return;
        }

        if (($game = $this->getPlugin()->getGameByPlayer($sender)) === null) {
            if ($sender->hasPermission("redrover.create")) {
                $sender->sendForm(new CreateForm());
                return;
            }

            $sender->sendForm(new JoinForm());
            return;
        }

        if ($game->getCreator() === $sender) {
            $sender->sendForm(new ManageForm());
            return;
        }

        $sender->sendForm(new WaitingForm());
    }
}