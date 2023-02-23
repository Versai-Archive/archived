<?php

/**
 * Author Versai
 * 
 * version 1.0.0
 * 
 * This file is to be used as a manager and manage all of the leveling happening
 */

 declare(strict_types=1);

 namespace Versai\RPGCore\Listeners;

 use pocketmine\item\Item;
 use pocketmine\event\entity\EntityDeathEvent;
 use pocketmine\event\block\BlockBreakEvent;
 use pocketmine\event\Listener;
 use pocketmine\item\ItemIds;
 use Versai\RPGCore\Utils\Utils;
 use function var_dump;


 class LevelListener implements Listener {
    # Sword

    /*public function entityDeath(EntityDeathEvent $event) {
         $player = $event->getEntity();
    }*/

    //get what item was used to kill the player.

    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $nbt = $item->getNamedTag();
        
        $pickaxes = [
            ItemIds::WOODEN_PICKAXE,
            ItemIds::STONE_PICKAXE,
            ItemIds::IRON_PICKAXE,
            ItemIds::GOLDEN_PICKAXE,
            ItemIds::DIAMOND_PICKAXE
        ];
        $shovels = [
            ItemIds::WOODEN_SHOVEL,
            ItemIds::STONE_SHOVEL,
            ItemIds::IRON_SHOVEL,
            ItemIds::GOLDEN_SHOVEL,
            ItemIds::DIAMOND_SHOVEL
        ];
        $axes = [
            ItemIds::WOODEN_AXE,
            ItemIds::STONE_AXE,
            ItemIds::IRON_AXE,
            ItemIds::GOLDEN_AXE,
            ItemIds::DIAMOND_AXE
        ]; //Stop using hardcoded values

        /**
         * Do not update the lore every block break be sure to make a task that wil update all items lore so that the lag will be reduced.
         */


        if (in_array($item->getId(), $pickaxes)) { //If item used was a pickaxe
            $level = $nbt->getInt("level", 0);
            $nbt->setInt("xp", $nbt->getInt("xp") + 1);

            var_dump($nbt->getInt("xp") . " - New XP on Item");
        } elseif (in_array($item->getId(), $shovels)) { // If item was a shovel
            $level = $nbt->getInt("level", 0);
            $xp = $nbt->getInt("xp", 0);

            $nbt->setInt("xp", ($xp + 1));
        } elseif (in_array($item->getId(), $axes)) { // if the item was an axe
            $level = $nbt->getInt("level", 0);
            $xp = $nbt->getInt("xp", 0);

            $nbt->setInt("xp", ($xp + 1));
        }
    }
 }