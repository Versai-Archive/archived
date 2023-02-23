<?php

namespace Versai\vStaff\math;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

class Entities {

	public static function getNearestEntityLookingAt(Entity $e, $range = -1) : ?Entity {
		return (!self::getNearbyEntitiesLookingAt($e, $range)) ? null : self::getNearbyEntitiesLookingAt($e, $range)[0];
	}

	public static function getNearbyEntitiesLookingAt(Entity $entity, float $area = 1) : ?array {
		$matches = [];
		$level = $entity->getWorld();
		$entities = $level->getEntities();
		foreach($entities as $e) {
			if(abs($entity->getPosition()->distance($e->getPosition()->asVector3())) > $area){
			    continue;
            }
			if(self::isLookingAt($entity, $e)){
			    array_push($matches, $e);
            } else {
			    continue;
			}
		}
		return (empty($matches)) ? null : $matches;
	}

	public static function isLookingAt(Entity $seer, Entity $target) : ?bool {
		$eyeVector = self::getEyeVector3($seer);
		$toEntity = self::getEyeVector3($target)->subtract($eyeVector->getX(), $eyeVector->getY(), $eyeVector->getZ());
		$dot = $toEntity->normalize()->dot(self::getDirection($seer));
		return $dot > 0.99;
	}

	public static function getEyeVector3(Entity $e): Vector3 {
		return new Vector3($e->getPosition()->x, $e->getPosition()->y + $e->getEyeHeight(), $e->getPosition()->z);
	}

	public static function getDirection(Entity $entity) : ?Vector3{
		$rotX = $entity->getLocation()->getYaw();
		$rotY = $entity->getLocation()->getPitch();

		$y = -sin(deg2rad($rotY));
		$xz = cos(deg2rad($rotY));
		$x = -$xz * sin(deg2rad($rotX));
		$z = $xz * cos(deg2rad($rotX));

		return (new Vector3($x, $y, $z));
	}
}