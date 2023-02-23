<?php

declare(strict_types=1);

namespace Versai\RPGCore\Entities;

use pocketmine\entity\Living;
use pocketmine\entity\Attribute;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;

abstract class VanillaEntity extends Living {

    private bool $baby = false;

    

}