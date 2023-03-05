<?php
declare(strict_types = 1);

namespace Versai\Duels\Items\Entity;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\world\particle\SplashParticle;

class FishingHook extends Projectile {

    public float $width = 0.25;
    public float $length = 0.25;
    public float $height = 0.25;
    protected $gravity = 0.1;
    protected $drag = 0.05;

    public static function getNetworkTypeId(): string{
        return EntityIds::FISHING_HOOK;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo{
        return new EntitySizeInfo($this->height, $this->width);
    }

    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null){
        parent::__construct($location, $shootingEntity, $nbt);
    }

    public function onUpdate(int $currentTick): bool{
        if($this->isFlaggedForDespawn() || !$this->isAlive()){
            return false;
        }

        $this->timings->startTiming();

        $hasUpdate = parent::onUpdate($currentTick);

        if($this->isCollidedVertically){
            $this->motion->x = 0;
            $this->motion->y += 0.01;
            $this->motion->z = 0;
            $hasUpdate = true;
        }elseif($this->isCollided && $this->keepMovement === true){
            $this->motion->x = 0;
            $this->motion->y = 0;
            $this->motion->z = 0;
            $this->keepMovement = false;
            $hasUpdate = true;
        }

        if($this->isOnGround()){
            $this->getWorld()->addParticle($this->getPosition(), new SplashParticle());
            $this->flagForDespawn();
        }

        $this->timings->stopTiming();

        return $hasUpdate;
    }

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void{
        (new ProjectileHitEntityEvent($this, $hitResult, $entityHit))->call();

        $owning = $this->getOwningEntity();
        if($entityHit instanceof Player && $owning instanceof Player) {
            if ($entityHit->getName() !== $owning->getName()) {
                $damage = $this->getResultDamage();

                if ($this->getOwningEntity() === null) {
                    $ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
                } else {
                    $ev = new EntityDamageByChildEntityEvent($owning, $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
                }

                $entityHit->attack($ev);

                $kx = $this->getDirectionVector()->getX() / 2.5;
                $kx = $kx - $kx * 2;
                $kz = $this->getDirectionVector()->getZ() / 2.5;
                $ky = 0.372;
                if ($entityHit->isOnGround() === false) {
                    $ky = 0;
                }

                $knockback = new Vector3($kx, $ky, $kz);
                $entityHit->setMotion($knockback);
                //$entityHit->setMotion($this->getOwningEntity()->getDirectionVector()->multiply(-0.3)->add(0, 0.3, 0));
                $this->isCollided = true;
                $this->flagForDespawn();
            }
        }
    }

    public function getResultDamage(): int{
        return 1;
    }
}


