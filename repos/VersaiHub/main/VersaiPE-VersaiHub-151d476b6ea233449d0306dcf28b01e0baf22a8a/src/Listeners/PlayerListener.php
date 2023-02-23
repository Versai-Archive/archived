<?php

declare(strict_types=1);

namespace Versai\Listeners;

use pocketmine\block\Chest;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\BlazeShootSound;
use Versai\Hotbars\HotbarItem;
use Versai\Main;
use Versai\Tasks\ElderGuardianTask;

class PlayerListener implements Listener {

    public int $cooldown = 5;

    public function onPlayerJoin(PlayerJoinEvent $event): void {

		$player = $event->getPlayer();
        $player->setGamemode(GameMode::ADVENTURE());

        $event->setJoinMessage("§7[§a+§7] {$player->getName()}");

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ElderGuardianTask($player), 10);
		Main::getInstance()->getHotbarManager()->getHotbar("MAIN")->sendTo($player);
        Main::getInstance()->getSessionManager()->registerSession($player);

        $session = Main::getInstance()->getSessionManager()->getSession($event->getPlayer());

        if (Main::getInstance()->getDatabase()->playerInDatabase($event->getPlayer())) {
            $data = Main::getInstance()->getDatabase()->getPlayerData($event->getPlayer()->getXuid())[0];
            $session->setKills((int)$data["kills"]);
        }
	}

	public function onPlayerQuit(PlayerQuitEvent $event): void {
		$player = $event->getPlayer();
        Main::getInstance()->getSessionManager()->unregisterSession($player);
		$event->setQuitMessage("§7[§c-§7] {$player->getName()}");
	}

	// i hate brady <3
	public function onPlayerItemUse(PlayerItemUseEvent $event) {
        $item = $event->getItem();

        if ($item instanceof HotbarItem) $item->handleInteraction();
	}

	public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();

        if ($event->getCause() == EntityDamageEvent::CAUSE_FALL) $event->cancel();

        if ($event instanceof EntityDamageByEntityEvent) {

            $damager = $event->getDamager();

            if ($entity instanceof Player || $damager instanceof Player) {

                [$entitySession, $damagerSession] = [Main::getInstance()->getSessionManager()->getSession($entity), Main::getInstance()->getSessionManager()->getSession($damager)];

                if (!$entitySession?->isPvpEnabled() || !$damagerSession?->isPvpEnabled()) $event->cancel();
            }
        }
	}

    // Optional Permissions Later
    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();

        if (!Main::getInstance()->getServer()->isOp($player->getName())) $event->cancel();
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();

        if (!Main::getInstance()->getServer()->isOp($player->getName())) $event->cancel();
    }

    public function onItemDrop(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();

        if (!Main::getInstance()->getServer()->isOp($player->getName())) $event->cancel();
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();

        $player->setGamemode(GameMode::SPECTATOR());

        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($player) : void {
            if ($this->cooldown > 0) {
                $this->cooldown--;

                if ($player->isConnected()) {
                    $player->sendTitle(TextFormat::RED . "You Died...", TextFormat::WHITE . "Respawning in: " . $this->cooldown);
                    return;
                }

                if ($player->isConnected()) {
                    $player->setGamemode(GameMode::ADVENTURE());
                }
                throw new CancelTaskException();
            }
            throw new CancelTaskException();
        }), 20);
        
        $event->setDrops([]);

        Main::getInstance()->getHotbarManager()->getHotbar("MAIN")->sendTo($player);
    }
}
