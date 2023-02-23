<?php

declare(strict_types = 1);

/**
 * This file is in charge of all event listeners
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Listeners;

use pocketmine\block\Chest;
use pocketmine\block\inventory\BarrelInventory;
use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Snowball;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\world\particle\FloatingTextParticle;
use Versai\RPGCore\Main;
use Versai\RPGCore\Tasks\UpdateLoreTask;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\world\Explosion;
use pocketmine\world\Position;
use pocketmine\event\entity\
{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\event\inventory\{
	InventoryTransactionEvent
};
use pocketmine\event\player\{PlayerCreationEvent,
    PlayerExhaustEvent,
    PlayerItemHeldEvent,
    PlayerItemUseEvent,
    PlayerJoinEvent,
    PlayerQuitEvent,
    PlayerRespawnEvent,
    PlayerDeathEvent,
    PlayerChatEvent,
    PlayerInteractEvent};
use pocketmine\Server;
use Versai\RPGCore\Sessions\{
	PlayerSession,
	SessionManager
};
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use pocketmine\network\mcpe\protocol\types\inventory\MismatchTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\NormalTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\ReleaseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\TransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use Versai\RPGCore\Utils\Emoji;
use function in_array;

class EventListener implements Listener {

	/** @var Main **/
    private Main $plugin;

	/**
	* EventListener Constructor.
	*
	* @param Main $plugin
	**/
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
    }

	/**
	* @param PlayerJoinEvent $event
	**/
	public function onJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();

		$event->setJoinMessage("§7[§a+§7] {$player->getName()}");

		$session = $this->plugin->getSessionManager();
  		$session->registerSession($player);

		Main::getInstance()->getScheduler()->scheduleRepeatingTask(new UpdateLoreTask($player), (20 * 5));
		Main::getInstance()->getBossBar()->addPlayer($player);

        //if ($player->getLevel() <= 0) {
        //    $player->setLevel(1);
        //}
    }

	public function onQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();

		$session = $this->plugin->getSessionManager();

		$playerSession = $session->getSession($player);

		Main::getInstance()->getBossBar()->removePlayer($player);

  		$session->unregisterSession($player);

		$event->setQuitMessage("§7[§c-§7] {$player->getName()}");
	}

	/**
	* @param PlayerRespawnEvent $event
	**/

	/*public function onRespawn(PlayerRespawnEvent $event) : void {
        $player = $event->getPlayer();
    }*/

    /*public function onInteract(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $data = $item->getNamedTag();
        if (!$item->hasNamedTag()) {
            $player->sendMessage("Item does not have a named tag");
            return;
        }
        $nbt = $data->getCompoundTag("versai:data_holding");
        $cooldown = $nbt->getInt("cooldown");
        $mana = $nbt->getInt("mana");

        if ($player->hasItemCooldown($item)) {
            $player->sendMessage("Item has a cooldown");
            return;
        }

        $sessionManager = $this->plugin->getSessionManager();
        $session = $sessionManager->getSession($player);

        if ($session->getMana() < $mana) {
            $player->sendMessage("Not enough mana");
            return;
        }

        $session->setMana($session->getMana() - $mana);

        $lb = $player->getTargetBlock(10);
        $la = $lb->getPosition();

        $dmger = new Arrow($player->getLocation(), $player, false);

        $dmger->canCollideWith($player);
        $dmger->setBaseDamage(4);
        $dmger->setMotion($la);

        $player->resetItemCooldown($item, $cooldown * 20);
    }*/

	/**
	* @param PlayerExhaustEvent $event
	**/
	public function onExhaust(PlayerExhaustEvent $event) : void {
        $event->cancel(); # Cancels hunger lose
    }

	public function onChat(PlayerChatEvent $event) : void {
		$player = $event->getPlayer();
		$message = $event->getMessage();
		$rank = Server::getInstance()->getPluginManager()->getPlugin('Hierarchy')->getMemberFactory()->getMember($player)->getTopRole()->getName();

		$message = str_replace([":heart:", ":blue:", ":fire:", ":l:", ":flower:", ":flowerb:", ":bluedot:", ":smile:"], [Emoji::HEART, "", "", "", "", "", "", ""], $message);

		$event->setFormat("§7[{$rank}§7] {$player->getName()} > {$message}");
	}

	/**
	* @param EntityDamageEvent $event
	**/
	public function onDamage(EntityDamageEvent $event) : void {
        $entity = $event->getEntity();
		$cause = $event->getCause();
		if ($event->isCancelled()) {
			return;
		}
		if (!($entity instanceof Player)) {
            return;
        }

		if ($cause == EntityDamageEvent::CAUSE_FALL) {
			$event->cancel();
			return;
		}
		if ($entity->getHealth() <= 0) { # Prevent crash from "dividing by zero" for calculateSeverity()
			return;
		}
		$this->sendTitleTo(
			$entity,
			$this->calculateSeverity((int)$event->getFinalDamage(), (int)$entity->getHealth())
		);
    }

	/**
	* Sends a player damage indicating title
	*
	* @param Entity|Player $entity
	* @param string $severity
	**/
	public function sendTitleTo($entity, string $severity) : void {
		$entity->sendTitle($severity."|              |", " ", 5, 20, 5);
	}

	/**
	* Calculates damage severity
	*
	* @param int $dealt
	* @param int $health
	*
	* @return string
	**/
	public function calculateSeverity(int $dealt, int $health) : string {
		$percentage = ($dealt / $health) * 100; # Calculates percentage
		$result = match(true)
		{
			$percentage >= 35 => TF::RED,
			$percentage >= 20 => TF::GOLD,
			$percentage < 20 => TF::YELLOW,
		};

		return $result;
	}
}