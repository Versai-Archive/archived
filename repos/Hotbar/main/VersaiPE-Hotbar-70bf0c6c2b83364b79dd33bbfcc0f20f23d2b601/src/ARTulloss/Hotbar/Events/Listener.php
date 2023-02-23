<?php
declare(strict_types = 1);

namespace ARTulloss\Hotbar\Events;

use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\ItemIds;
use pocketmine\world\World;
use pocketmine\scheduler\ClosureTask;
use ARTulloss\Hotbar\Main;
use pocketmine\player\Player;
use function in_array;
use function array_values;
use ReflectionException;

class Listener implements PMListener {

	/** @var Main $plugin */
	private Main $plugin;

	/**
	 * Observer constructor
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * Gives player Hotbar on join if they have one assigned
	 * @param $event
	 * @priority HIGHEST
     * @throws ReflectionException
	 */
	public function onJoin(PlayerJoinEvent $event): void {
		$player = $event->getPlayer();
		$level = $player->getWorld();
		if(!$player->isClosed()) {// If kicked
            $this->bindPlayerLevelHotbar($player, $level);
        }
	}

	/**
     * Deregister them from plugin
	 * @param PlayerQuitEvent $event
	 * @priority HIGHEST
     * @throws ReflectionException
	 */
	public function onLeave(PlayerQuitEvent $event): void {
	    $player = $event->getPlayer();
	    $users = $this->plugin->getHotbarUsers();
	    if($users->getHotbarFor($player) !== null) {
	        $users->remove($player);
        }
	}

	/**
	 * Gives player Hotbar on respawn
	 * @param PlayerRespawnEvent $event
	 * @priority HIGHEST
     * @throws ReflectionException
	 */
	public function onRespawn(PlayerRespawnEvent $event): void {
	    $player = $event->getPlayer();
	    $level = $player->getWorld();
	    $this->bindPlayerLevelHotbar($player, $level);
	}

	/**
	 * Gives player Hotbar on level change
	 * @param EntityTeleportEvent $event
	 * @priority HIGHEST
     * @throws ReflectionException
	 */
	public function switchWorld(EntityTeleportEvent $event): void {
		$player = $event->getEntity();
		$from = $event->getFrom();
		$to = $event->getTo();
		if($from->getWorld()->getFolderName() !== $to->getWorld()->getFolderName()) {
            if ($player instanceof Player) {
                $this->bindPlayerLevelHotbar($player, $to->getWorld());
            }
        }
	}

    /**
     * Sends a player the hotbar for a world, or not if none exists
     * @param Player $player
     * @param World $level
     * @throws ReflectionException
     */
	private function bindPlayerLevelHotbar(Player $player, World $level): void {
        $hotbar = $this->plugin->getHotbarLevels()->getHotbarForLevel($level);
        $users = $this->plugin->getHotbarUsers();
        if($hotbar !== null) {
            $users->assign($player, $hotbar);
        } else {
            $users->remove($player);
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @throws ReflectionException
     */
	public function onInteract(PlayerInteractEvent $event): void {
	    $player = $event->getPlayer();
	    $users = $this->plugin->getHotbarUsers();
	    $hotbarUser = $users->getHotbarFor($player);
	    if($hotbarUser !== null) {
            if($this->plugin->getServer()->getTick() - $hotbarUser->getLastUsage() <= $this->plugin->getConfig()->get('Cooldown')) {
                $event->cancel();
            } else {
                $hotbar = $hotbarUser->getHotbar();
                $inv = $player->getInventory();
                $index = $inv->getHeldItemIndex();
                $items = $hotbar->getItems();
                $item = $inv->getItem($index);
                if(isset($items[$index + 1]) && ($hotbarItem = $items[$index + 1]) && $item->getName() === $hotbarItem->getName()
                    && $item->getId() === $hotbarItem->getId() && $item->getMeta() === $hotbarItem->getMeta()) {
                    // Hack, remove in 4.0.0 ?
                    $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($hotbarUser, $player, $hotbar, $index): void {
                        $hotbar->execute($player, $index);
                        (new UseHotbarEvent($hotbarUser, $index))->call();
                    }), 0);
                    $hotbarUser->updateLastUsage();
                } elseif($item->getId() !== ItemIds::AIR) {
                    $users->remove($player, false);
                }
            }
        }
	}

	/**
	 * Blocks moving items in specified worlds if the user is still assigned a hotbar
	 * @param InventoryTransactionEvent $event
	 */
	public function moveInventory(InventoryTransactionEvent $event): void {
	    $player = $event->getTransaction()->getSource();
        $this->lock($player, $event);
	}

    /**
     * @param EntityItemPickupEvent $event
     */
	public function onPickupItem(EntityItemPickupEvent $event): void {
        $inv = $event->getInventory();
        if($inv instanceof PlayerInventory) {
            $player = $inv->getHolder();
            if($player instanceof Player) {
                $this->lock($player, $event);
            }
        }
    }

    /**
     * @param Player $player
     * @param Event $event
     */
    public function lock(Player $player, Event $event): void {
        $level = $player->getWorld();
        $levelName = $level->getDisplayName();
        if (in_array($levelName, $this->plugin->getConfig()->get('Locked Inventory'), true) && $this->plugin->getHotbarUsers()->getHotbarFor($player) !== null) {
            $event->cancel();
        }
    }
}
