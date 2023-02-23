<?php
declare(strict_types=1);

namespace ARTulloss\Arenas\Player;

use ARTulloss\Arenas\Arenas;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player as PMPlayer;

class Player extends PMPlayer{

    /** @var float $groundLevel */
    public $groundLevel;

    //Generic knockBack function.
    public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4): void{
        $f = sqrt($x * $x + $z * $z);
        if($f <= 0){
            return;
        }
        if(mt_rand() / mt_getrandmax() > $this->getAttributeMap()->getAttribute(Attribute::KNOCKBACK_RESISTANCE)->getValue()){
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $base;
            $motion->y += $base * (mt_rand(112,115) / 10);
            $motion->z += $z * $f * $base;

            if($motion->y > $base){
                $motion->y = $base;
            }

            //Kinda hacky ig. Thanks adam
            // Specifically for combo :P
            if($base === Arenas::getInstance()->arenas["Checkerboard"]->getKnockback()) {
                if($this->isOnGround()) {
                    $this->groundLevel = $this->y;
                } else {
                    if (($this->y - $this->groundLevel) > 1.9) {
                        $motion->y = .1;
                    }
                }
            }

            $this->setMotion($motion);
        }
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source) : void{
        if($this->attackTime > 0){
            if($this->getLastDamageCause() !== null){
                $source->setCancelled();
            }
        }
        parent::attack($source);
    }
}