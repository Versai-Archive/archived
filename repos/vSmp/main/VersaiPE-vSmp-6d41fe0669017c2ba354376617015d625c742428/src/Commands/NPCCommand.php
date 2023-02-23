<?php

declare(strict_types=1);

namespace Versai\RPGCore\Commands;

use alvin0319\CustomItemLoader\items\SMPItemIds;
use alvin0319\CustomItemLoader\items\SMPItemLoader;
use pocketmine\nbt\tag\CompoundTag;
use Versai\RPGCore\Main;

use pocketmine\permission\DefaultPermissions;
use pocketmine\command\{
	Command,
	CommandSender
};
use pocketmine\plugin\{
	PluginOwned,
	PluginOwnedTrait
};
use pocketmine\entity\Villager;
use alvin0319\CustomItemLoader\CustomItemManager;

class NPCCommand extends Command implements PluginOwned {

    use PluginOwnedTrait;

    public function __construct($name, $description) {
        parent::__construct($name, $description);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage("§d[SYSTEM] §cNo authorization detected");
            $sender->sendMessage("§d[SYSTEM] §cYou may not use command: NPC");
            return false;
        }

        if (!isset($args[0])) {
            $sender->sendMessage("§cUsage: /npc <type>");
            return;
        }
        
        switch($args[0]) {
            case "bs":
            case "blacksmith":

                $pos = $sender->getLocation();
                $villager = new Villager($pos);
                $villager->setImmobile(true);
                $villager->setNameTag("§7§lBlacksmith");
                $villager->setNameTagAlwaysVisible(true);
                $villager->setCanSaveWithChunk(true);
                $villager->setScoreTag("bs_npc");
                $villager->setSilent(true);
                $villager->setProfession(Villager::PROFESSION_BLACKSMITH);
                $villager->spawnTo($sender);
            break;

            case "test":
                $item = SMPItemLoader::getItem(SMPItemIds::CRYSTAL_SWORD);

                $sender->getInventory()->setItemInHand($item);
            break;
        }
    }
}