<?php

namespace Bavfalcon9\StaffHUD\Math;

use pocketmine\math\Vector3;
use pocketmine\entity\Entity;

class Entities {
    private $pos1;

    public function __construct(Vector3 $position, float $yaw, float $pitch) {
        $this->pos1 = $position;
        $this->yaw = $yaw;
        $this->pitch = $pitch;
    }

    /**
     * Get the exact block ahead of the player based on position.
     */
    public function getExactVector(float $eyeHeight, int $blocksAhead): ?Vector3 {
        $floorLevel = $this->pos1;
        $eyeLevel = new Vector3($floorLevel->getX(), $floorLevel->getY() + $eyeHeight, $floorLevel->getZ());
        
    }

    /**
     * Return an array of entities in line of sight.
     */
    public function getInLineOfSight(Level $level): ?Array {

    }

    public static function isLookingAt(Entity $seer, Entity $target): ?Bool {
        $eyeVector = self::getEyeVector3($seer);
        $toEntity = self::getEyeVector3($target)->subtract($eyeVector);
        $dot = $toEntity->normalize()->dot(self::getDirection($seer));
        return $dot > 0.99;
    }

    public static function getEyeVector3(Entity $e) {
        return new Vector3($e->x, $e->y + $e->getEyeHeight(), $e->z);
    } 

    public static function getNearestEntity(Entity $e, $range=2): ?Entity {
        return (!self::getNearbyEntities($e, $range)) ? null : self::getNearbyEntities($e, $range)[0];
    }

    public static function getNearestEntityLookingAt(Entity $e, $range=-1): ?Entity {
        return (!self::getNearbyEntitiesLookingAt($e, $range)) ? null : self::getNearbyEntitiesLookingAt($e, $range)[0];
    }

    public static function getNearbyEntitiesLookingAt(Entity $entity, float $area=1): ?Array {
        $matches = [];
        $level = $entity->getLevel();
        $entities = $level->getEntities();
        foreach ($entities as $e) {
            if (abs($entity->distance($e)) > $area) continue;
            if (self::isLookingAt($entity, $e)) array_push($matches, $e);
            else continue;
        }
        return (empty($matches)) ? null : $matches;
    }

    public static function getNearbyEntities(Entity $entity, float $area=1): ?Array {
        $matches = [];
        $level = $entity->getLevel();
        $entities = $level->getEntities();
        foreach ($entities as $e) {
            if (abs($entity->distance($e)) <= $area) array_push($matches, $e);
            else continue;
        }
        return (empty($matches)) ? null : $matches;
    }

    public static function getDirection(Entity $entity): ?Vector3 {
        $rotX = $entity->getYaw();
        $rotY = $entity->getPitch();

        $y = -sin(deg2rad($rotY));
        $xz = cos(deg2rad($rotY));
        $x = -$xz * sin(deg2rad($rotX));
        $z = $xz * cos(deg2rad($rotX));

        return new Vector3($x, $y, $z);
    }

}