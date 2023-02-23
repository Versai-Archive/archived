<?php

namespace Versai\vStaff\math;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function abs;
use function pow;
use function sqrt;

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

    public static function getDistance(Player $dam, Player $p): float|int{
        $dx = $dam->getPosition()->getX();
        $dy = $dam->getPosition()->getY();
        $dz = $dam->getPosition()->getZ();
        $px = $p->getPosition()->getX();
        $py = $p->getPosition()->getY();
        $pz = $p->getPosition()->getZ();

        $distanceX = sqrt(pow(($px - $dx), 2) + pow(($py - $dy), 2));
        $distanceZ = sqrt(pow(($pz - $dz), 2) + pow(($py - $dy), 2));
        return (abs($distanceX) > abs($distanceZ)) ? abs($distanceX) : abs($distanceZ);
    }
}