<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/12/2018
 * Time: 2:17 PM
 */

declare(strict_types=1);

namespace ARTulloss\Arenas\Events;

use ARTulloss\Arenas\Arenas;
use ARTulloss\Arenas\Player\Player as ArenaPlayer;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

/**
 * Class Observer
 * @package ARTulloss\Arenas\Events
 */
class Observer implements Listener
{

	/** @var Arenas */
	private $main;
	/** @var $blocks */
	private $blocks;

	/**
	 * Observer constructor.
	 * @param Arenas $main
	 */
	public function __construct(Arenas $main)
	{
		$this->main = $main;
	}

	/**
	 * @param EntityDamageEvent $event
	 *
	 * @priority LOW
	 */
	public function onDamage(EntityDamageEvent $event): void
	{

		$entity = $event->getEntity();
		$levelName = $entity->getLevel()->getName();

		if (isset($this->main->arenas[$levelName])) {

			if ($event instanceof EntityDamageByEntityEvent && $entity instanceof Player) {

				// Spawn protection, simplified!

				if ($this->main->arenas[$levelName]->getLocation()->distance($entity->getPosition()) < $this->main->arenas[$levelName]->getProtection())
					$event->setCancelled();

				$event->setKnockBack($this->main->arenas[$levelName]->getKnockback());
				$event->setAttackCooldown($this->main->arenas[$levelName]->getHitCooldown());

			} elseif (!$this->main->arenas[$levelName]->hasFallDamage() && $event->getCause() === EntityDamageEvent::CAUSE_FALL)
				$event->setCancelled(); // Anti fall damage
		}
	}

	/**
	 * @param InventoryPickupItemEvent $event
	 */
	public function onPickUpItem(InventoryPickupItemEvent $event): void
	{
		$levelName = $event->getItem()->getLevel()->getName();

		if (isset($this->main->arenas[$levelName]) && !$this->main->arenas[$levelName]->canPickUpItems())
			$event->setCancelled();
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function onHunger(PlayerExhaustEvent $event): void
	{
		$player = $event->getEntity();
		$levelName = $player->getLevel()->getName();
		if (isset($this->main->arenas[$levelName]) && !$this->main->arenas[$levelName]->hasHunger() && $player instanceof Player) {
			$player->setFood(20);
			$event->setCancelled();
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onBlockBreak(BlockBreakEvent $event): void
	{
		$player = $event->getPlayer();
		$levelName = $player->getLevel()->getName();

		// Don't allow the arena to be damaged!

		if (isset($this->main->arenas[$levelName]) && !$this->main->arenas[$levelName]->isBreakable()) {
            $event->setCancelled();
        } elseif(isset($this->main->arenas[$levelName]) && $this->main->arenas[$levelName]->isBreakable()){
		    $breakList = $this->main->arenas[$levelName]->getBreakableList();
		    if(!in_array($event->getBlock()->getId(), $breakList)){
		        $event->setCancelled();
            }
        }
	}

	/**
	 * @param BlockPlaceEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onBlockPlace(BlockPlaceEvent $event): void
	{
		$player = $event->getPlayer();
		$levelName = $player->getLevel()->getName();

		// Don't allow the arena to be damaged!

		if (isset($this->main->arenas[$levelName]) && !$this->main->arenas[$levelName]->isPlaceable()) {
            $event->setCancelled();
        } elseif(isset($this->main->arenas[$levelName]) && $this->main->arenas[$levelName]->isPlaceable()){
            $placeList = $this->main->arenas[$levelName]->getPlaceableList();
            if(!in_array($event->getBlock()->getId(), $placeList)){
                $event->setCancelled();
            }elseif($event->getBlock()->y > $this->main->arenas[$levelName]->getBuildLimit()){
                $event->setCancelled();
            }
        }
	}

	/**
	 * @param PlayerDeathEvent $event
	 *
	 * @priority HIGH
	 */
	public function onDeath(PlayerDeathEvent $event): void
	{

		$player = $event->getPlayer();

		$level = $player->getLevel();

		if (!isset($this->main->arenas[$levelName = $level->getName()]) || $player->getGamemode() === Player::SPECTATOR)
			return;

		$arena = $this->main->arenas[$levelName];

		if ($arena->hasLightning())
			$this->sendLightning($player, $level);
		if ($arena->hasExplosion()) {
			$bomb = new Explosion($player->getPosition(), 1);
			$bomb->explodeB();
		}
	}

	/**
	 * @param Player $player
	 * @param Level|null $level
	 */
	public function sendLightning(Player $player, Level $level = null): void{
		if ($level === null)
			$level = $player->getLevel();
		$lightning = new AddActorPacket();
		$lightning->type = AddActorPacket::LEGACY_ID_MAP_BC[EntityIds::LIGHTNING_BOLT];
		$lightning->entityRuntimeId = Entity::$entityCount++;
		$lightning->metadata = [];
		$lightning->position = $player->asVector3()->add(0, $height = 0);
		$lightning->yaw = $player->getYaw();
		$lightning->pitch = $player->getPitch();
		$player->getServer()->broadcastPacket($level->getPlayers(), $lightning);
	}

	/**
	 * @param DataPacketReceiveEvent $event
	 */
	public function onDataPacket(DataPacketReceiveEvent $event): void
	{
		$pk = $event->getPacket();
		if ($pk instanceof LevelSoundEventPacket && ($level = $event->getPlayer()->getLevel()) && ($level !== null) && ($levelName = $level->getName()) && isset($this->main->arenas[$levelName])) {
			switch ($pk->sound) {
				case LevelSoundEventPacket::SOUND_HIT:
				case LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE:
				case LevelSoundEventPacket::SOUND_FALL:
				case LevelSoundEventPacket::SOUND_THROW:
				case LevelSoundEventPacket::SOUND_ATTACK_STRONG:
					break;
				default:
					$event->setCancelled();
			}
		}
	}

	public function onPlayerCreation(PlayerCreationEvent $event): void
    {
	    $event->setPlayerClass(ArenaPlayer::class);
    }

}
