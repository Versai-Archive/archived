<?php

declare(strict_types=1);

/**
 * This file is in charge of loading all of the features
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Indicators;

use Ramsey\Uuid\Uuid;
use Versai\RPGCore\Main;
use Versai\RPGCore\Tasks\IndicatorRemoveTask;
use Ramsey\Uuid\UuidInterface;
use pocketmine\event\Listener;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use ReflectionClass;
use pocketmine\world\Position;
use pocketmine\utils\SingletonTrait;
use pocketmine\item\{
    ItemFactory,
    ItemIds
};
use pocketmine\network\mcpe\protocol\{
	AddPlayerPacket,
	RemoveActorPacket,
	AdventureSettingsPacket 
};
use pocketmine\network\mcpe\protocol\types\entity\{
    EntityMetadataFlags,
    EntityMetadataProperties,
    FloatMetadataProperty,
    LongMetadataProperty
};
use pocketmine\event\entity\
{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};

use pocketmine\utils\TextFormat as C;

class IndicatorManager implements Listener {

    use SingletonTrait;

	public $eid;
	public Main $plugin;
	
	public function __construct(Main $plugin) {
		self::$instance = $this;
        $this->plugin = $plugin;
    }
	
	public function onDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();
		$cause = $event->getCause();
		$damage = $event->getFinalDamage();
		if ($event->isCancelled()) {
			return;
		}
		if ($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			if ($damager === $entity) return;
			if ($event->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0) {
				$this->addTag($entity->getPosition(), 1, $damage, C::RED, true);
			} else {
				$this->addTag($entity->getPosition(), 1, $damage, C::RED, false);
			}
		}
    }
	
	/**
	* Adds a damage display tag
	*
	* @param Position $pos
	* @param int      $y
	* @param int      $damage
	* @param bool $critical
	* @param string   $color
	**/
	public function addTag(Position $pos, $y, $damage, string $color, bool $critical = false){
		$packet = new AddPlayerPacket();
		$id = Entity::nextRuntimeId();
		$packet->actorRuntimeId = $id;
		$packet->actorUniqueId = $id;
		$this->eid[$id] = true;
		$packet->position = $pos->add(0,$y,0);
		$uuid = Uuid::uuid4();
		$packet->uuid = $uuid;
		$packet->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet(ItemFactory::air()));
		if ($critical === true) {
			$data = "§eCRIT ".$color."-❤".$damage;
		} else {
			$data = $color."-❤".$damage;
		}
		$packet->username = $data;
		$flags = (1 << EntityMetadataFlags::IMMOBILE);
	
		$packet->metadata = [
			EntityMetadataProperties::FLAGS => new LongMetadataProperty($flags),
			EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.01)
		];
		
		$packet->adventureSettingsPacket = AdventureSettingsPacket::create(0, 0, 0, 0, 0, $id);
	
		foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $player) {
			$player->getNetworkSession()->sendDataPacket($packet);
		}
		Main::getInstance()->getScheduler()->scheduleDelayedTask(new IndicatorRemoveTask($id), 20);
	}
	
	/**
	* Removes a damage display tag
	*
	* @param int $eid
	**/
	public function removeTag($eid) {
		if (isset($this->eid[$eid])) {
			$packet = new RemoveActorPacket();
			$packet->actorUniqueId = $eid;
			foreach(Main::getInstance()->getServer()->getOnlinePlayers() as $players) {
				$players->getNetworkSession()->sendDataPacket($packet);
			}
			unset($this->eid[$eid]);
		}
	}
}