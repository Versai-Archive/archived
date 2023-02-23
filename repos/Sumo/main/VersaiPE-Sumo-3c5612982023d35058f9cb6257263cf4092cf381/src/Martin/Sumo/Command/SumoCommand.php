<?php


namespace Martin\Sumo\Command;


use Martin\GameAPI\Command\BaseGameCommand;
use Martin\GameAPI\Types\GameStateType;
use Martin\Sumo\Command\SubCommands\CloseCommand;
use Martin\Sumo\Command\SubCommands\CreateCommand;
use Martin\Sumo\Command\SubCommands\CreatorCommand;
use Martin\Sumo\Command\SubCommands\JoinCommand;
use Martin\Sumo\Command\SubCommands\LeaveCommand;
use Martin\Sumo\Command\SubCommands\SpectateCommand;
use Martin\Sumo\Command\SubCommands\StartCommand;
use Martin\Sumo\Form\CreateForm;
use Martin\Sumo\Form\ManagerForm;
use Martin\Sumo\Main;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SumoCommand extends BaseGameCommand
{
    protected function prepare(): void
    {
        $this->registerSubCommand(new CloseCommand($this->getPlugin(), "close", "Close the current tournament you're hosting"));
        $this->registerSubCommand(new CreatorCommand($this->getPlugin(), "creator", "Create a sumo tournament map"));
        $this->registerSubCommand(new CreateCommand($this->getPlugin(), "create", "Create a sumo tournament game"));
        $this->registerSubCommand(new JoinCommand($this->getPlugin(), "join", "Join up on a sumo tournament game"));
        $this->registerSubCommand(new LeaveCommand($this->getPlugin(), "leave", "Leave a sumo tournament"));
        $this->registerSubCommand(new StartCommand($this->getPlugin(), "start", "Start a sumo tournament"));
        $this->registerSubCommand(new SpectateCommand($this->getPlugin(), "spectate", "Spectate a sumo tournamnet"));
        #   $this->registerSubCommand(new testcmd($this->getPlugin(), "test", "test"));
    }

    protected function onRunEmptyArguments(CommandSender $sender, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (($game = $this->getPlugin()->getGameByPlayer($sender)) !== null) {
            if ($game->getCreator() === $sender) {
                if ($game->getCurrentState() === GameStateType::STATE_WAITING) {
                    $sender->sendForm(new ManagerForm());
                } else {
                    $sender->sendMessage(Main::PREFIX . TextFormat::RED . "You can't manage this game if it already started");
                }
            } else {
                $sender->sendMessage(Main::PREFIX . TextFormat::RED . "You can't execute this command if you are not the creator (If you want to leave us /sumo leave)");
            }

            return;
        }

        if ($sender->hasPermission("sumo.games")) {
            if ($this->getPlugin()->hasMaps()) {
                $sender->sendForm(new CreateForm($this->getPlugin()));
            } else {
                $sender->sendMessage(Main::PREFIX . TextFormat::RED . "No maps are currently loaded!");
            }
        } else {
            $sender->sendMessage(Main::PREFIX . TextFormat::RED . "You need to be a Voter or higher to execute this command!");
        }
    }
}