<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/22/2020
 * Time: 12:54 PM
 */
declare(strict_types=1);

namespace ARTulloss\TwistedKits\Utilities;

use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Utilities{
    /**
     * @param Player $player
     * @return Vector3
     */
    static function calculateRelativePosition(Player $player): Vector3{
        $position = $player->asVector3();
        $direction = $player->getDirectionVector();
        $subtract = $direction->multiply(0.75);
        $position = $position->add($subtract);
        $position->y += ($player->getEyeHeight() - 0.2);
        return $position;
    }
    /**
     * @param Item $item
     * @param Player $player
     */
    static function createItemEntity(Item $item, Player $player): void{
        $nbt = ItemEntity::createBaseNBT(self::calculateRelativePosition($player), null, lcg_value() * 360, 0);
        $itemTag = $item->nbtSerialize();
        $itemTag->setName("Item");
        $nbt->setShort("Health", 5);
        $nbt->setShort("PickupDelay", 20);
        $nbt->setTag($itemTag);
        $itemEntity = Entity::createEntity('Item', $player->getLevel(), $nbt);
        //$itemEntity->spawnTo($player);
        $itemEntity->spawnToAll();
    }
}