<?php

declare(strict_types = 1);
namespace ARTulloss\Duels\Items;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\level\sound\LaunchSound;
use pocketmine\math\Vector3;

use ARTulloss\Duels\Items\Entity\FishingHook;
use pocketmine\player\Player;

/**
 * Class FishingRod
 * @package ARTulloss\Duels\Items
 * @author CortexPE
 */
class FishingRod extends Durable
{
	/**
	 * FishingRod constructor.
	 * @param int $meta
	 */
	public function __construct($meta = 0)
	{
		parent::__construct(new ItemIdentifier(ItemIds::FISHING_ROD, $meta),'Fishing Rod');
	}

	/**
	 * @return int
	 */
	public function getMaxStackSize(): int
	{
		return 1;
	}

	/**
	 * @return int
	 */
	public function getMaxDurability(): int
	{
		return 355;
	}

	/**
	 * @return int
	 */
	public function getCooldownTicks(): int
	{
		return 8;
	}

	/**
	 * @param Player $player
	 * @param Vector3 $directionVector
	 * @return bool
     */
	public function onClickAir(Player $player, Vector3 $directionVector):bool
	{
		$nbt = EntityFactory::getInstance()->createBaseNBT($player->add(0, $player->getEyeHeight(), 0), $directionVector, $player->yaw, $player->pitch);

        $projectile = new FishingHook($directionVector, '', $)

		$projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);
		if ($projectile !== null) {
			$projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));
		}

		if ($projectile instanceof Projectile) {
			$projectileEv = new ProjectileLaunchEvent($projectile);
			$projectileEv->call();

			if ($projectileEv->isCancelled()) {
				$projectile->flagForDespawn();
			} else {
				$projectile->spawnToAll();
				$player->getLevel()->addSound(new LaunchSound($player), $player->getViewers());
			}
		}
		return true;
	}

	/**
	 * @return string
	 */
	public function getProjectileEntityType(): string{
		return "FishingHook";
	}

	/**
	 * @return float
	 */
	public function getThrowForce(): float{
		return 1.6;
	}
}