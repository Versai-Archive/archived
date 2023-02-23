<?php

namespace Bavfalcon9\StaffHUD;

use Bavfalcon9\StaffHUD\Main;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;

use pocketmine\event\player\{
    PlayerCommandPreprocessEvent,
    PlayerQuitEvent,
    PlayerJoinEvent,
    PlayerInteractEvent,
    PlayerMoveEvent,
    PlayerDropItemEvent,
    PlayerEditBookEvent
};
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\inventory\InventoryEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\{
    Player,
    Server
};


class EventManager implements Listener {
    private $plugin;
    
    public function __construct(Main $pl) {
        $this->plugin = $pl;
    }

    public function onDrop(PlayerDropItemEvent $event) {
        $this->plugin->staffHUD->handleDrop($event);
    }
    
    public function onTransaction(InventoryTransactionEvent $event) {
        $this->plugin->staffHUD->handleTransaction($event);
    }

    public function onOpenInventory(InventoryOpenEvent $event) {
        $this->plugin->staffHUD->handleOpenInv($event);
    }

    public function onInteract(PlayerInteractEvent $event) {
        $this->plugin->staffHUD->handleClick($event);
    }

    public function onJoin(PlayerJoinEvent $event) {
        $this->plugin->staffHUD->handleJoin($event);
    }
    
    public function onLeave(PlayerQuitEvent $event) {
        $this->plugin->staffHUD->handleQuit($event);
    }

    public function onBreak(BlockBreakEvent $event) {
        $this->plugin->staffHUD->handleBreak($event);
    }

    public function onPlace(BlockPlaceEvent $event) {
        $this->plugin->staffHUD->handlePlace($event);
    }

    public function onMove(PlayerMoveEvent $event) {
        $this->plugin->staffHUD->handleMove($event);
    }

    public function onDamage(EntityDamageByEntityEvent $event) {
        $this->plugin->staffHUD->handleDamage($event);
    }
    public function onBookWrite(PlayerEditBookEvent $event) {
        $this->plugin->staffHUD->handleWriteBook($event);
    }
    public function onTeleport(EntityTeleportEvent $event) {
        $this->plugin->staffHUD->handleTeleport($event);
    }
}