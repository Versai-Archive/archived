<?php

declare(strict_types = 1);

/**
 * This file is in charge of all event listeners
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Listeners;

use pocketmine\entity\Entity;
use Versai\RPGCore\Main;
use Versai\RPGCore\RPGPlayer;
use Versai\RPGCore\Tasks\UpdateLoreTask;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use Versai\RPGCore\Entities\Zombie;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use Versai\RPGCore\Data\SQLDataStorer;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\
{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\event\player\
{
	PlayerCreationEvent,
	PlayerExhaustEvent,
	PlayerJoinEvent,
	PlayerQuitEvent,
	PlayerRespawnEvent,
	PlayerDeathEvent,
	PlayerChatEvent
};
use pocketmine\Server;
use Versai\RPGCore\Sessions\{
	PlayerSession,
	SessionManager
};
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
	* @param PlayerCreationEvent $event
	**/
	public function onCreation(PlayerCreationEvent $event) : void {
        $event->setPlayerClass(RPGPlayer::class);
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

        if ($player->getLevel() <= 0) {
            $player->setLevel(1);
        }
    }

	public function onQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();

		$session = $this->plugin->getSessionManager();

		$playerSession = $session->getSession($player);

		$data = new SQLDataStorer($this->plugin);


		$data->registerPlayer($player);

		$data->setPlayerData(
			// $playerSession->getClass(),
			// $playerSession->getMaxMana(),
			// $playerSession->getDefense(),
			// $playerSession->getAgility(),
			// $playerSession->getCoins(),
			// $playerSession->getQuestId(),
			// $playerSession->getQuestProgress()
			$playerSession
		);

  		$session->unregisterSession($player);

		$event->setQuitMessage("§7[§c-§7] {$player->getName()}");
	}
	
	/**
	* @param PlayerDeathEvent $event
	**/
	public function onDeath(PlayerDeathEvent $event) : void {
		$player = $event->getPlayer();
		$player->level = $player->getLevel();
	}
	
	/**
	* @param PlayerRespawnEvent $event
	**/
	public function onRespawn(PlayerRespawnEvent $event) : void {
        $player = $event->getPlayer();
        if ($player instanceof RPGPlayer) {
            $player->setLevel($player->level); # Keep XP level
			
            //$player->applyAgility();
        }
    }
	
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

	public function onMobSpawn(EntitySpawnEvent $event) {
        $entity = $event->getEntity();

        if ($entity instanceof Zombie) {
            var_dump("Entity spawned Zombie new Class");
            $entity->setNameTagAlwaysVisible(true);
            $entity->setNameTag(TF::DARK_PURPLE . "[" . TF::LIGHT_PURPLE . "2" . TF::DARK_PURPLE . "] " . TF::DARK_GREEN . $entity->getName() . " " . TF::DARK_RED . "[" . TF::RED . $entity->getHealth() . TF::GRAY . "/" . TF::RED . $entity->getMaxHealth() . TF::DARK_RED . "]");
        } else {
            return;
        }
    }

    public function entityDamageEvent(EntityDamageByEntityEvent $event) {
        $entity = $event->getEntity();

        if ($entity instanceof Zombie) {
            $entity->setNameTag(TF::DARK_PURPLE . "[" . TF::LIGHT_PURPLE . "2" . TF::DARK_PURPLE . "] " . TF::DARK_GREEN . $entity->getName() . " " . TF::DARK_RED . "[" . TF::RED . $entity->getHealth() . TF::GRAY . "/" . TF::RED . $entity->getMaxHealth() . TF::DARK_RED . "]");
        } else {
            return;
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();

        foreach($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy(16.0, 16.0, 16.0), $player) as $entity) {
            if ($entity instanceof Zombie) {
                $angle = atan2($player->getLocation()->getZ() - $entity->getLocation()->getZ(), $player->getLocation()->getX() - $entity->getLocation()->getX());
		        $yaw = (($angle * 180) / M_PI) - 90;
		        $angle = atan2((new Vector2($entity->getLocation()->x, $entity->getLocation()->z))->distance(new Vector2($player->getLocation()->x, $player->getLocation()->z)), $player->getLocation()->y - $entity->getLocation()->y);
		        $pitch = (($angle * 180) / M_PI) - 90;

                $x = $player->getLocation()->asVector3()->getX() - $entity->getLocation()->asVector3()->getX();
                $y = $player->getLocation()->asVector3()->getY() - $entity->getLocation()->asVector3()->getY();
                $z = $player->getLocation()->asVector3()->getZ() - $entity->getLocation()->asVector3()->getZ();
                $diff = abs($x) + abs($z);

                $vec = new Vector3($x, 0, $z);

                $entity->setRotation($yaw, $pitch);
                $entity->setMotion($vec);
                // disabled movement for now
            } else {
                return;
            }
        }
    }
}