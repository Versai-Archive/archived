<?php

namespace impl\sexypots;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		Entity::registerEntity(SplashPotion::class, true, ["ThrownPotion"]);
		ItemFactory::registerItem(new class extends \pocketmine\item\SplashPotion {
			public function getThrowForce(): float {
				return 0.51;
			}
		}, true);
	}

	public function onPacket(DataPacketReceiveEvent $event): void {
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		if ($packet instanceof InventoryTransactionPacket) {
			$trData = $packet->trData;
			if ($trData instanceof UseItemTransactionData && $trData->getActionType() === UseItemTransactionData::ACTION_CLICK_AIR && ($item = $trData->getItemInHand()->getItemStack()) instanceof \pocketmine\item\SplashPotion && $player->getInventory()->getItem($trData->getHotbarSlot()) instanceof \pocketmine\item\SplashPotion) {
				/** @var \pocketmine\item\SplashPotion $item */
				if ($player->hasItemCooldown($item) || $player->isSpectator()) {
					return;
				}
				$directionVector = $player->getDirectionVector();
				$playerPos = $trData->getPlayerPos()->add($directionVector->x * 0.15, 0, $directionVector->z * 0.15);
				$nbt = Entity::createBaseNBT($playerPos, $directionVector, $player->yaw, $player->pitch);
				$nbt->setShort("PotionId", $item->getDamage());

				$projectile = Entity::createEntity($item->getProjectileEntityType(), $player->getLevelNonNull(), $nbt, $player);
				if ($projectile !== null) {
					$projectile->setMotion($projectile->getMotion()->multiply($item->getThrowForce()));
				}
				$item->pop();

				if ($projectile instanceof Projectile) {
					$projectileEv = new ProjectileLaunchEvent($projectile);
					$projectileEv->call();
					if ($projectileEv->isCancelled()) {
						$projectile->flagForDespawn();
					} else {
						$projectile->spawnToAll();
						$player->getLevelNonNull()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_THROW, 0, EntityIds::PLAYER);
					}
				} elseif ($projectile !== null) {
					$projectile->spawnToAll();
				} else {
					return;
				}

				if ($player->isSurvival()) {
					$player->getInventory()->setItem($trData->getHotbarSlot(), $item);
				}
				$event->setCancelled();
			}
		}
	}

}