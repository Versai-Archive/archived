<?php

declare(strict_types=1);

namespace Versai\RPGCore\Quests;

use pocketmine\player\Player;
use pocketmine\event\player\{
    PlayerJoinEvent
};
use pocketmine\event\entity\{
    EntityDamageByEntityEvent
};
use pocketmine\event\block\{
    BlockBreakEvent,
    BlockPlaceEvent
};
use pocketmine\event\inventory\{
    CraftItemEvent
};
use pocketmine\event\Listener;
use Versai\RPGCore\Main;
use Versai\RPGCore\Quests\Quest;
use xenialdan\apibossbar\BossBar;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use array_key_exists;

class QuestListener implements Listener {

    private Main $plugin;
    
    public function __construct(Main $plugin) {
		$this->plugin = $plugin;
    }

    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $manager = $this->plugin->getSessionManager();
        $session = $manager->getSession($player);

        $quests = yaml_parse_file($this->plugin->getDataFolder() . "quests.yml");

        if (!$session->getQuestId()) {
            return;
        }

        $quest = $quests[$session->getQuestId()];

        if(!$quest) {
            return;
        }

        if ($quest["type"] != Quest::QUEST_TYPE_BLOCK_BREAK) {
            return;
        }

        if(!$session->getQuest()) {
            var_dump("Quest not found for {$player->getName()}");
            return;
        } else {
            
            if ($session->getQuest()->getType() !== Quest::QUEST_TYPE_BLOCK_BREAK) {
                return;
            }

            if (!array_key_exists("reqId", $quest)) {
                $session->setQuestProgress($session->getQuestProgress() + 1);
                return;
            }

            if (!$quest["reqId"] || $quest["reqId"] === null) {
                $session->setQuestProgress($session->getQuestProgress() + 1);
                return;
            }

            if ($quest["reqId"] == $block->getId()) {
                $session->setQuestProgress($session->getQuestProgress() + 1);
                return;
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        $manager = $this->plugin->getSessionManager();
        $session = $manager->getSession($player);

        $quests = yaml_parse_file($this->plugin->getDataFolder() . "quests.yml");

        if (!$session->getQuestId()) {
            return;
        }

        $quest = $quests[$session->getQuestId()];

        if(!$quest) {
            return;
        }

        if ($quest["type"] != Quest::QUEST_TYPE_BLOCK_PLACE) {
            return;
        }

        if(!$session->getQuest()) {
            var_dump("Quest not found for {$player->getName()}");
            return;
        } else {
            
            if ($session->getQuest()->getType() !== Quest::QUEST_TYPE_BLOCK_PLACE) {
                return;
            }

            $session->setQuestProgress($session->getQuestProgress() + 1);
        }
    }

    public function onEntityDie(EntityDamageByEntityEvent $event) {
        $entity = $event->getEntity();
        $player = $event->getDamager();

        $sessionManager = $this->plugin->getSessionManager();
        $session = $sessionManager->getSession($player);

        $quests = yaml_parse_file($this->plugin->getDataFolder() . "quests.yml");
        $quest = $quests[$session->getQuestId()];

        if(!$quest) {
            return;
        }

        if ($quest["type"] != Quest::QUEST_TYPE_KILL_MOB) {
            return;
        }

        if (($entity->getHealth() - $event->getFinalDamage()) > 0) {
            return;
        }

        if(!$session->getQuest()) {
            var_dump("Quest not found for {$player->getName()}");
            return;
        } else {
            
            if ($session->getQuest()->getType() !== Quest::QUEST_TYPE_KILL_MOB) {
                return;
            }

            $session->setQuestProgress($session->getQuestProgress() + 1);
        }
    }

    public function onCraft(CraftItemEvent $event) {
        $player = $event->getPlayer();
        $items = $event->getOutputs();

        $sessionManager = $this->plugin->getSessionManager();
        $session = $sessionManager->getSession($player);

        $quests = yaml_parse_file($this->plugin->getDataFolder() . "quests.yml");
        $quest = $quests[$session->getQuestId()];

        if(!$quest) {
            return;
        }

        if ($quest["type"] != Quest::QUEST_TYPE_CRAFT_ITEM) {
            return;
        }

        if(!$session->getQuest()) {
            var_dump("Quest not found for {$player->getName()}");
            return;
        } else {
            if ($session->getQuest()->getType() !== Quest::QUEST_TYPE_CRAFT_ITEM) {
                return;
            }

            if (!array_key_exists("reqId", $quest)) {
                $session->setQuestProgress($session->getQuestProgress() + 1);
                return;
            }

            if (!$quest["reqId"] || $quest["reqId"] === null) {
                $session->setQuestProgress($session->getQuestProgress() + 1);
                return;
            }

            foreach($items as $item) {
                if ($quest["reqId"] == $item->getId()) {
                    $session->setQuestProgress($session->getQuestProgress() + 1);
                    return;
                } else {
                    return;
                }
            }
        }
    }
}