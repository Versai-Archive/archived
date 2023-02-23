<?php

namespace Versai\arenas\commands;

use pocketmine\command\CommandSender;
use Versai\arenas\Arenas;
use Versai\arenas\commands\subcommands\CreateSubCommand;
use Versai\arenas\commands\subcommands\InfoSubCommand;
use Versai\arenas\commands\subcommands\ListSubCommand;
use Versai\arenas\commands\subcommands\RemoveSubCommand;
use Versai\arenas\commands\subcommands\SetSubCommand;
use Versai\arenas\Constants;
use Versai\arenas\libs\CortexPE\Commando\BaseCommand;

class ArenaCommand extends BaseCommand implements Constants{

    private Arenas $plugin;

    public function __construct(Arenas $plugin, string $name, string $description = "", array $aliases = []){
        $this->plugin = $plugin;
        parent::__construct($plugin, $name, $description, $aliases);
    }

    public function prepare(): void{
        $this->setPermission(self::BASE_PERMISSION);
        $this->registerSubCommand(new ListSubCommand($this->plugin, "list", "List all arenas!"));
        $this->registerSubCommand(new InfoSubCommand($this->plugin, "info", "List info about an arena!"));
        $this->registerSubCommand(new CreateSubCommand($this->plugin, "create", "Create a new arena!"));
        $this->registerSubCommand(new RemoveSubCommand($this->plugin, "remove", "Remove an arena!"));
        $this->registerSubCommand(new SetSubCommand($this->plugin, "set", "Set/Change settings for an arena!"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        $this->sendUsage();
    }
}