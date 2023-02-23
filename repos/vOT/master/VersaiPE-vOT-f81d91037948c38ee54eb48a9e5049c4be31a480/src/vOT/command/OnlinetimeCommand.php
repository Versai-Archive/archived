<?php declare(strict_types=1);

namespace vOT\command;

use pocketmine\command\CommandSender;
use vOT\command\sub\LastseenCommand;
use vOT\command\sub\SessionCommand;
use vOT\command\sub\TotalCommand;
use vOT\libs\CortexPE\Commando\BaseCommand;
use vOT\Loader;

class OnlinetimeCommand extends BaseCommand {
    protected Loader $plugin;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        parent::__construct($plugin, "onlinetime", "Onlinetime Command", ["ot", "vt", "otime", "vtime"]);
    }

    protected function prepare(): void {
        $this->registerSubCommand(new TotalCommand($this->plugin));
        $this->registerSubCommand(new SessionCommand($this->plugin));
        $this->registerSubCommand(new LastseenCommand($this->plugin));
    }

    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $this->sendUsage();
    }
}