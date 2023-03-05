<?php
declare(strict_types = 1);

namespace ARTulloss\Duels\Items\Entity;

use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\level\particle\SplashParticle;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;

/**
 * Class FishingHook
 * @package ARTulloss\Duels\Items\Entity
 * @author CortexPE
 */
class FishingHook extends Projectile {

	public const NETWORK_ID = self::FISHING_HOOK;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;
	protected $gravity = 0.1;
	protected $drag = 0.05;

	public function onUpdate(int $currentTick): bool
	{
		if($this->isFlaggedForDespawn() || !$this->isAlive()) {
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

		if($this->isOnGround()) {
			$this->getLevel()->addParticle(new SplashParticle($this->getPosition()));
			$this->flagForDespawn();
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}

	/**
	 * @param Entity $entityHit
	 * @param RayTraceResult $hitResult
	 * @throws \ReflectionException
	 */
	public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
	{
		(new ProjectileHitEntityEvent($this, $hitResult, $entityHit))->call();

		$damage = $this->getResultDamage();

		if($this->getOwningEntity() === null){
			$ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
		}else{
			$ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
		}

		$entityHit->attack($ev);

		$kx = $this->getDirectionVector()->getX() / 2.5;
		$kx = $kx - $kx * 2;
		$kz = $this->getDirectionVector()->getZ() / 2.5;
        $ky = 0.372;
		if($entityHit->isOnGround() === false){
		    $ky = 0;
        }

		$knockback = new Vector3($kx, $ky, $kz);
		$entityHit->setMotion($knockback);
		$this->isCollided = true;
		$this->flagForDespawn();
	}

	/**
	 * @return int
	 */
	public function getResultDamage(): int
	{
		return 1;
	}
}