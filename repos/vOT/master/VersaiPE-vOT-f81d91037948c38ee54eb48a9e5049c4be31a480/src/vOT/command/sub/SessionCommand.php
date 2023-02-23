<?php declare(strict_types=1);

namespace vOT\command\sub;

use pocketmine\command\CommandSender;
use vOT\libs\CortexPE\Commando\args\TextArgument;
use vOT\libs\CortexPE\Commando\BaseSubCommand;
use vOT\Loader;

class SessionCommand extends BaseSubCommand {
    protected Loader $plugin;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        parent::__construct("session", "A command to get the session OT of a player", ["s"]);
    }

    protected function prepare(): void {
        $this->registerArgument(0, new TextArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $username = isset($args["name"]) ? $args["name"] : $sender->getName();
        $time = $this->plugin->getDB()->getSessionTime($username);
        $sender->sendMessage("§aSession time of §e{$username}§a: §e{$time}");
    }
}