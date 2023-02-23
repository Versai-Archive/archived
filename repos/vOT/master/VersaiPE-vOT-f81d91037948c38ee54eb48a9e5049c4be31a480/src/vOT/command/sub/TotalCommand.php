<?php declare(strict_types=1);

namespace vOT\command\sub;

use pocketmine\command\CommandSender;
use vOT\libs\CortexPE\Commando\args\TextArgument;
use vOT\libs\CortexPE\Commando\BaseSubCommand;
use vOT\Loader;

class TotalCommand extends BaseSubCommand {
    protected Loader $plugin;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        parent::__construct("total", "A command to get the total OT of a player", ["t"]);
    }

    protected function prepare(): void {
        $this->registerArgument(0, new TextArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $alias, array $args): void {
        $username = isset($args["name"]) ? $args["name"] : $sender->getName();
        if($this->plugin->getDB()->getTotalTime($username, function($has) use ($sender, $username) {
            if(!$has) {
                $sender->sendMessage("§cThe player §e{$username} §chas has never logged on Versai.");
                return;
            } else {
                $this->plugin->getDB()->getTotalTime($username, function($time) use ($sender, $username): void {
                    $sender->sendMessage("§aTotal time of §e{$username}§a: §e{$time}");
                });
            }
        }));
    }
}
