<?php
declare(strict_types=1);

namespace Versai\arenas\player;

use pocketmine\entity\Attribute;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use Versai\arenas\Arenas;

class ArenaPlayer extends Player{

    public float $groundLevel;

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.390): void{
        $f = sqrt($x * $x + $z * $z);
        if($f <= 0){
            return;
        }
        if(mt_rand() / mt_getrandmax() > $this->getAttributeMap()->get(Attribute::KNOCKBACK_RESISTANCE)->getValue()){
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $force;
            $motion->y += $force * (mt_rand(112,115) / 10);
            $motion->z += $z * $f * $force;

            if($motion->y > $verticalLimit){
                $motion->y = $verticalLimit;
            }

            if($force === Arenas::getInstance()->arenas["Checkerboard"]->getKnockback()){
                if($this->isOnGround()) {
                    $this->groundLevel = $this->getPosition()->getY();
                } else {
                    if (($this->getPosition()->getY() - $this->groundLevel) > 1.9) {
                        $motion->y = .1;
                    }
                }
            } else {
                if($this->isOnGround()) {
                    $this->groundLevel = $this->getPosition()->getY();
                } else {
                    if (($this->getPosition()->getY() - $this->groundLevel) > 2.5) {
                        $motion->y = .1;
                    }
                }
            }

            $this->setMotion($motion);
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