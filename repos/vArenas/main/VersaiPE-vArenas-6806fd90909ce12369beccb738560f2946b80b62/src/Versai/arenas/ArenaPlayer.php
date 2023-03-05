<?php
declare(strict_types=1);

namespace Versai\arenas;

use pocketmine\entity\Attribute;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function mt_getrandmax;
use function mt_rand;
use function sqrt;

class ArenaPlayer extends Player
{
    public float $groundLevel = 0;

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void{
        $f = sqrt($x * $x + $z * $z);
        if($f <= 0){
            return;
        }
        if(mt_rand() / mt_getrandmax() > $this->getAttributeMap()->get(Attribute::KNOCKBACK_RESISTANCE)->getValue()){
            $f = 1 / $f;

            $motionX = $this->motion->x / 2;
            $motionY = $this->motion->y / 2;
            $motionZ = $this->motion->z / 2;
            $motionX += $x * $f * $force;
            $motionY += $force * (mt_rand(112,115) / 10);;
            $motionZ += $z * $f * $force;

            $verticalLimit ??= $force;
            if($motionY > $verticalLimit){
                $motionY = $verticalLimit;
            }

            if($force === 0.33){
                if($this->isOnGround()) {
                    $this->groundLevel = $this->getPosition()->getY();
                } else {
                    if (($this->getPosition()->getY() - $this->groundLevel) > 1.9) {
                        $motionY = .1;
                    }
                }
            } else {
                if($this->isOnGround()) {
                    $this->groundLevel = $this->getPosition()->getY();
                } /*else {
                    if (($this->getPosition()->getY() - $this->groundLevel) > 2.8) {
                        $motionY = .1;
                    }
                }*/
            }

            $this->setMotion(new Vector3($motionX, $motionY, $motionZ));
        }
    }

    public function attack(EntityDamageEvent $source) : void{
        if($this->attackTime > 0){
            if($this->getLastDamageCause() !== null){
                $source->cancel();
            }
        }
        parent::attack($source);
    }
}