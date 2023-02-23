<?php

declare(strict_types=1);

namespace Versai\RPGCore\Quests;

use pocketmine\player\Player;
use pocketmine\event\player\{
    PlayerJoinEvent
};
use pocketmine\event\block\{
    BlockBreakEvent
};
use pocketmine\event\Listener;
use Versai\RPGCore\Main;

class QuestListener implements Listener {

    private Main $plugin;
    
    public function __construct(Main $plugin) {
		$this->plugin = $plugin;
    }
}