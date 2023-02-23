<?php

namespace Versai\vStaff;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class EventListener implements Listener {

	private Main $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	public function onDrop(PlayerDropItemEvent $event) {
		$this->plugin->vstaff->handleDrop($event);
	}

	public function onTransaction(InventoryTransactionEvent $event) {
		$this->plugin->vstaff->handleTransaction($event);
	}

	public function onJoin(PlayerJoinEvent $event) {
		$this->plugin->vstaff->handleJoin($event);
	}

	public function onLeave(PlayerQuitEvent $event) {
		$this->plugin->vstaff->handleQuit($event);
	}

	public function onBreak(BlockBreakEvent $event) {
		$this->plugin->vstaff->handleActions($event);
	}

	public function onPlace(BlockPlaceEvent $event) {
		$this->plugin->vstaff->handleActions($event);
	}

	public function onMove(PlayerMoveEvent $event) {
		$this->plugin->vstaff->handleMove($event);
	}

	public function onInteract(PlayerItemUseEvent $event) {
		$this->plugin->vstaff->handleClick($event);
	}

	public function onDamage(EntityDamageByEntityEvent $event) {
		$this->plugin->vstaff->handleDamage($event);
	}

	public function onTeleport(EntityTeleportEvent $event) {
		$this->plugin->vstaff->handleTeleport($event);
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) {
		if(($player = $event->getOrigin()->getPlayer()) !== null) {
            $p = $event->getPacket();
            if ($p instanceof LevelSoundEventPacket and $p->sound == LevelSoundEvent::ATTACK_NODAMAGE or $p instanceof InventoryTransactionPacket and $p->trData->getTypeId() === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) {
                if (isset($this->plugin->cps[$player->getName()])) {
                    $this->plugin->addClick($player);
                }
            }
        }
	}

}