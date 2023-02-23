<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 10/12/2018
 * Time: 9:13 AM
 */

declare(strict_types=1);

namespace ARTulloss\Tap2;

use ARTulloss\Tap2\Utilities\DeviceOS;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

/**
 * Class Tap2Pot
 * @package ARTulloss\Tap2Pot
 */
class Tap2 extends PluginBase implements Listener
{

	private const ENTITY_TYPE = 'ThrownPotion';
	private const THROW_FORCE = 0.5;
	/** @var DeviceOS $deviceOS */
	private $deviceOS;

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->deviceOS = new DeviceOS();
	}
    /**
     * @param DataPacketReceiveEvent $event
     */
	public function onLogin(DataPacketReceiveEvent $event): void{
	    $pk = $event->getPacket();
	    if($pk instanceof LoginPacket) {
	        $this->deviceOS->setDeviceOS($pk->username, $pk->clientData['DeviceOS']);
        }
    }
	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onTap(PlayerInteractEvent $event): void{
		if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_AIR) {

			$player = $event->getPlayer();

			if($this->deviceOS->isPE($player)) {

                $inventory = $player->getInventory();

                $hand = $inventory->getItemInHand();

                if ($hand->getId() === Item::SPLASH_POTION) {

                    $nbt = SplashPotion::createBaseNBT($player->add(0, $player->getEyeHeight(), 0), $player->getDirectionVector(), $player->yaw, $player->pitch);
                    $projectile = SplashPotion::createEntity(self::ENTITY_TYPE, $player->getLevel(), $nbt);

                    if ($projectile !== null) {
                        $projectile->setMotion($projectile->getMotion()->multiply(self::THROW_FORCE));
                        $projectile->setPotionId($hand->getDamage());
                    }

                    $inventory->setItemInHand(new Item(Item::AIR));
                }
            }
		}

		# Tap to equip

		$player = $event->getPlayer();
		$arm = $player->getArmorInventory();
		$inv = $player->getInventory();

		if (!$player instanceof Player)
			return;

		if ($arm->getHelmet()->getId() === Item::AIR) {
			if (\in_array($inv->getItemInHand()->getId(), Armor::HELMET)) {
				$arm->setHelmet($inv->getItemInHand());
				$inv->setItemInHand(Item::get(Item::AIR));
			}
		}

		if ($arm->getChestplate()->getId() === Item::AIR) {
			if (\in_array($inv->getItemInHand()->getId(), Armor::CHESTPLATE)) {
				$arm->setChestplate($inv->getItemInHand());
				$inv->setItemInHand(Item::get(Item::AIR));
			}
		}

		if ($arm->getLeggings()->getId() === Item::AIR) {
			if (\in_array($inv->getItemInHand()->getId(), Armor::LEGGINGS)) {
				$arm->setLeggings($inv->getItemInHand());
				$inv->setItemInHand(Item::get(Item::AIR));
			}
		}

		if ($arm->getBoots()->getId() === Item::AIR) {
			if (\in_array($inv->getItemInHand()->getId(), Armor::BOOTS)) {
				$arm->setBoots($inv->getItemInHand());
				$inv->setItemInHand(Item::get(Item::AIR));
			}
		}

	}
}
