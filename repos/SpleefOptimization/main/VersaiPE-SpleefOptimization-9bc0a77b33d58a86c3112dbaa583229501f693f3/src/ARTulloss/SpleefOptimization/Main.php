<?php

declare(strict_types=1);

namespace ARTulloss\SpleefOptimization;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {

	public function onEnable() : void{
	    $this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onBlockBreak(BlockBreakEvent $event): void{
	    if($event->getBlock()->getId() !== Block::SNOW_BLOCK)
	        return;
	    $player = $event->getPlayer();
	    $drops = $event->getDrops();
	    $event->setDrops([]);
	    foreach ($drops as $drop) {
            $player->getInventory()->addItem($drop);
        }
    }
}
