<?php
namespace Jibix\Disguise\command;
use Jibix\Disguise\disguise\DisguiseManager;
use Jibix\Disguise\form\DisguiseForm;
use Jibix\Disguise\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;


/**
 * Class DisguiseCommand
 * @package Jibix\Disguise\command
 * @author Jibix
 * @date 08.02.2022 - 23:31
 * @project Disguise
 */
class DisguiseCommand extends Command{

    public function __construct(){
        parent::__construct("disguise", "", "/disguise [add|delete|list|check] [name]", []);
        $this->setPermission("disguise.use");
    }

    /**
     * Function execute
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cPlease use this command ingame!");
            return;
        }
        if (!$sender->hasPermission("disguise.use")) {
            $sender->sendMessage("§cYou don't have the permission to use this command!");
            return;
        }
        if (empty($args[0])) {
            new DisguiseForm($sender);
            return;
        }
        $file = Main::getInstance()->getNameFile();
        $names = $file->get("names");
        switch (strtolower($args[0])) {
            case "add":
                if (isset($names[$args[0]])) {
                    $sender->sendMessage("§cThis name does already exist!");
                    return;
                }

                $file->set("names", array_merge([$args[0]], $names));
                $file->save();
                $sender->sendMessage("§aYou have added deleted the§b {$args[0]}§a name!");
                break;

            case "delete":
            case "remove":
            case "del":
                if (!isset($names[$args[0]])) {
                    $sender->sendMessage("§cThis name does not exist!");
                    return;
                }

                $file->removeNested("names.{$args[0]}");
                $file->save();
                $sender->sendMessage("§aYou have successfully deleted the§b {$args[0]}§a name!");
                break;

            case "list":
                $sender->sendMessage("§8-=§bDisguised Players§8=-");
                foreach (DisguiseManager::getInstance()->disguised as $realName => $data) {
                    $sender->sendMessage("§a{$realName}§8 =>§c {$data['disguise']}");
                }
                break;

            case "check":
                if (DisguiseManager::getInstance()->isDisguised($args[0])) {
                    $sender->sendMessage("§b{$args[0]}§a is disguised as§c " . DisguiseManager::getInstance()->disguised[$args[0]]['disguise'] . "§a!");
                } else {
                    $sender->sendMessage("§b{$args[0]}§c is not disguised!");
                }
                break;

            default:
                new DisguiseForm($sender);
        }
    }
}