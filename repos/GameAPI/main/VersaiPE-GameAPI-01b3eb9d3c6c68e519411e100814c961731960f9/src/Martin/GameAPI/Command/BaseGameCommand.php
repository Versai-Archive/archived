<?php


namespace Martin\GameAPI\Command;


use Martin\GameAPI\GamePlugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use Versai\RedRover\Commands\BaseSubCommand;

abstract class BaseGameCommand extends Command implements PluginIdentifiableCommand
{
    private GamePlugin $plugin;
    /** @var BaseGameSubCommand[] */
    private array $subCommands = [];

    private bool $shouldRunEmptyArgs = false;

    public function __construct(GamePlugin $plugin, string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->prepare();
    }

    abstract protected function prepare(): void;

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (empty($args[0])) {
            $this->onRunEmptyArguments($sender, $args);
            return;
        }

        $subCommandLabel = strtolower(array_shift($args));
        $subCommand = $this->getCommand($subCommandLabel);
        if ($subCommand === null) {

            return;
        }

        $subCommand->execute($sender, $subCommandLabel, $args);
    }

    abstract protected function onRunEmptyArguments(CommandSender $sender, array $args): void;

    public function getCommand(string $any): ?BaseGameSubCommand
    {
        $any = strtolower($any);
        if (isset($this->subCommands[$any])) {
            return $this->subCommands[$any];
        } else {
            foreach ($this->getSubCommands() as $subCommand) {
                if (in_array($any, $subCommand->getAliases())) {
                    return $subCommand;
                }
            }
        }

        return null;
    }

    /**
     * @return BaseGameSubCommand[]
     */
    public function getSubCommands(): array
    {
        return $this->subCommands;
    }

    public function registerSubCommand(BaseGameSubCommand $command): bool
    {
        if (isset($this->subCommands[$command->getName()])) return false;
        $this->subCommands[$command->getName()] = $command;
        return true;
    }

    /**
     * @return GamePlugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}