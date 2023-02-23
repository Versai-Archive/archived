<?php

declare(strict_types = 1);

/**
 * This file is in charge of registering all entities
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Entities;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;

class EntityManager {

	/**
	* Function for registering an Entity
	* 
	* @param string $class
	**/
	private static function register(string $class) {
        EntityFactory::getInstance()->register($class, function (World $world, CompoundTag $nbt) use ($class): Entity {
            return new $class(EntityDataHelper::parseLocation($nbt, $world));
        }, [$class]);
    }
	
    public static function init() : void {
        self::register(Zombie::class);
	}
}