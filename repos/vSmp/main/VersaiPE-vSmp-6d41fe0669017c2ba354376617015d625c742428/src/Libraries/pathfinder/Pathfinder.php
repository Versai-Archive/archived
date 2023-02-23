<?php
/*
 * Copyright (c) Matze997
 * All rights reserved.
 * Under GPL license
 */

declare(strict_types=1);

namespace Versai\RPGCore\Libraries\pathfinder;

use Versai\RPGCore\Libraries\pathfinder\command\PathfinderCommand;
use Versai\RPGCore\Libraries\pathfinder\entity\TestEntity;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\World;
use Versai\RPGCore\Entities\Goblin;

class Pathfinder extends PluginBase {
    public static $instance = null;

    protected function onEnable(): void{
        self::$instance = $this;

        Server::getInstance()->getCommandMap()->register("pathfinder", new PathfinderCommand());

        EntityFactory::getInstance()->register(Goblin::class, function(World $world, CompoundTag $nbt) : Goblin{
            return new Goblin(EntityDataHelper::parseLocation($nbt, $world), 2);
        }, ["Goblin"], EntityLegacyIds::GHAST);
    }
}