<?php declare(strict_types=1);

namespace vOT\command\sub;

use pocketmine\command\CommandSender;
use vOT\libs\CortexPE\Commando\args\TextArgument;
use vOT\libs\CortexPE\Commando\BaseSubCommand;
use vOT\Loader;

class LastseenCommand extends BaseSubCommand {
    protected Loader $plugin;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        parent::__construct("lastseen", "A command to get when a player was last seen", ["ls"]);
    }

    protected function prepare(): void {
        $this->registerArgument(0, new TextArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $username = isset($args["name"]) ? $args["name"] : $sender->getName();
        if($this->plugin->getDB()->getTotalTime($username, function($has) use ($sender, $username) {
            if(!$has) {
                $sender->sendMessage("§cThe player §e{$username} §chas has never logged on Versai.");
            } else {
                $this->plugin->getDB()->getLastSeen($username, function($time) use ($sender, $username): void {
                    $sender->sendMessage("§e{$username}§a was last seen at: §e{$time}");
                });
            }
        }));

    }
}