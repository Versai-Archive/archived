<?php

declare(strict_types=1);

namespace Versai\RPGCore\Listeners;

use pocketmine\event\Listener;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\entity\Zombie;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pathfinder\algorithm\astar;

class MobEventListener implements Listener {

    public function onMobSpawn(EntitySpawnEvent $event) {
        $entity = $event->getEntity();

        if ($entity instanceof Zombie) {
            var_dump("Entity spawned Zombie new Class");
            $entity->setNameTagAlwaysVisible(true);
            $entity->setNameTag(TF::DARK_PURPLE . "[" . TF::LIGHT_PURPLE . "2" . TF::DARK_PURPLE . "] " . TF::DARK_GREEN . $entity->getName() . " " . TF::DARK_RED . "[" . TF::RED . $entity->getHealth() . TF::GRAY . "/" . TF::RED . $entity->getMaxHealth() . TF::DARK_RED . "]");
        } else {
            return;
        }
    }

    public function entityDamageEvent(EntityDamageByEntityEvent $event) {
        $entity = $event->getEntity();

        if ($entity instanceof Zombie) {
            $entity->setNameTag(TF::DARK_PURPLE . "[" . TF::LIGHT_PURPLE . "2" . TF::DARK_PURPLE . "] " . TF::DARK_GREEN . $entity->getName() . " " . TF::DARK_RED . "[" . TF::RED . $entity->getHealth() . TF::GRAY . "/" . TF::RED . $entity->getMaxHealth() . TF::DARK_RED . "]");
        } else {
            return;
        }
    }

    //public function onPlayerMove(PlayerMoveEvent $event) {
        // $player = $event->getPlayer();

        // foreach($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy(16.0, 16.0, 16.0), $player) as $entity) {
        //     if ($entity instanceof Zombie) {
        //         $angle = atan2($player->getLocation()->getZ() - $entity->getLocation()->getZ(), $player->getLocation()->getX() - $entity->getLocation()->getX());
		//         $yaw = (($angle * 180) / M_PI) - 90;
		//         $angle = atan2((new Vector2($entity->getLocation()->x, $entity->getLocation()->z))->distance(new Vector2($player->getLocation()->x, $player->getLocation()->z)), $player->getLocation()->y - $entity->getLocation()->y);
		//         $pitch = (($angle * 180) / M_PI) - 90;

        //         $x = $player->getLocation()->asVector3()->getX() - $entity->getLocation()->asVector3()->getX();
        //         $y = $player->getLocation()->asVector3()->getY() - $entity->getLocation()->asVector3()->getY();
        //         $z = $player->getLocation()->asVector3()->getZ() - $entity->getLocation()->asVector3()->getZ();
        //         $diff = abs($x) + abs($z);

        //         $vec = (new Vector3($x, 0, $z))->multiply(0.2);

        //         $entity->setRotation($yaw, $pitch);
        //         $entity->setMotion($vec);
        //         // disabled movement for now
        //     } else {
        //         return;
        //     }
        // }
    //}

}