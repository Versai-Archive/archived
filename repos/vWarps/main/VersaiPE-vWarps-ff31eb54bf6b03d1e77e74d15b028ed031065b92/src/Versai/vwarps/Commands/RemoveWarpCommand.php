<?php
declare(strict_types=1);

namespace Versai\vwarps\Commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use Versai\vwarps\Main;

class RemoveWarpCommand extends PluginBaseCommand {

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument('name'));
        $this->setPermission(Main::PERMISSION_ROOT . 'delete');
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if(!$this->testPermission($sender)) {
            return;
        }
        $name = $args['name'];
        if(isset($args['name'])) {
            if($this->getPlugin()->getWarpsContainer()->removeWarpByName($name)) {
                $sender->sendMessage(TextFormat::GREEN . "Successfully deleted warp $name!");
            } else {
                $sender->sendMessage(TextFormat::RED . "No warp named $name exists!");
            }
        } else {
            $this->sendUsage();
        }
    }
}
