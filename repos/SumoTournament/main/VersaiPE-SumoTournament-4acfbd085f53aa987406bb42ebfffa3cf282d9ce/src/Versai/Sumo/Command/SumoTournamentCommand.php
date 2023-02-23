<?php


namespace Versai\Sumo\Command;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use Versai\Sumo\Command\SubCommands\CloseSubCommand;
use Versai\Sumo\Command\SubCommands\CreateSubCommand;
use Versai\Sumo\Command\SubCommands\HelpSubCommand;
use Versai\Sumo\Command\SubCommands\SetPosition1SubCommand;
use Versai\Sumo\Command\SubCommands\SetPosition2SubCommand;
use Versai\Sumo\Command\SubCommands\SetSpawnSubCommand;
use Versai\Sumo\Command\SubCommands\StartSubCommand;
use Versai\Sumo\Sumo;

class SumoTournamentCommand extends Command implements PluginIdentifiableCommand
{
    private Sumo $sumo;

    /**
     * @var BaseSubCommand[]
     */
    private array $subCommands = [];

    public function __construct(Sumo $sumo) {
        parent::__construct("sumo", $sumo->getMessageConfig()->get("command-description"), "/sumo");
        $this->sumo = $sumo;
        $this->registerSubCommands();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        $subCommand = strtolower(array_shift($args));
        $subCommand = $this->getSubCommand($subCommand);

        if ($subCommand === null) $sender->sendMessage($this->getPlugin()->getMessageConfig()->get("subcommand-not-found"));
        else $subCommand->onRun($sender, $args);
    }

    public function registerSubCommand(BaseSubCommand $command): bool {
        $currentUses = array_merge([$command->getName()], $command->getAliases());
        foreach ($this->subCommands as $subCommand) {
            $foreachCommandUses = array_merge([$subCommand->getName()], $subCommand->getAliases());
            $overlappingUses = array_intersect($currentUses, $foreachCommandUses);
            if (sizeof($overlappingUses) > 0) return false;
        }
        $this->subCommands[$command->getName()] = $command;
        return true;
    }

    public function registerSubCommands()
    {
        $this->registerSubCommand(new HelpSubCommand($this->getPlugin(), $this));
        $this->registerSubCommand(new CreateSubCommand($this->getPlugin(), $this));
        $this->registerSubCommand(new SetSpawnSubCommand($this->getPlugin(), $this));
        $this->registerSubCommand(new SetPosition1SubCommand($this->getPlugin(), $this));
        $this->registerSubCommand(new SetPosition2SubCommand($this->getPlugin(), $this));
        $this->registerSubCommand(new StartSubCommand($this->getPlugin(), $this));
        $this->registerSubCommand(new CloseSubCommand($this->getPlugin(), $this));
    }

    public function getSubCommand(string $any): ?BaseSubCommand {
        $any = strtolower($any);
        if (isset($this->subCommands[$any])) return $this->subCommands[$any];
        foreach ($this->subCommands as $subCommand) {
            if ($subCommand->getAliases() !== null)
                if (in_array($any, $subCommand->getAliases()))
                    return $subCommand;
        }

        return null;
    }

    /**
     * @return Sumo
     */
    public function getPlugin(): Plugin
    {
        return $this->sumo;
    }

    /**
     * @return BaseSubCommand[]
     */
    public function getSubCommands(): array
    {
        return $this->subCommands;
    }
}