<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Listener;

use pocketmine\block\Chest;
use pocketmine\block\tile\Chest as TChest;
use pocketmine\block\Gravel;
use pocketmine\block\Sand;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\object\FallingBlock;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\types\InteractionMode;
use pocketmine\player\Player;
use pocketmine\Server;
use Versai\OneBlock\Discord\Embed;
use Versai\OneBlock\Discord\Message;
use Versai\OneBlock\Events\Economy\SellEvent;
use Versai\OneBlock\Events\IslandCreateEvent;
use Versai\OneBlock\Events\IslandLevelUpEvent;
use Versai\OneBlock\Main;
use Versai\OneBlock\OneBlock\OneBlock;
use Versai\OneBlock\OneBlock\OneBlockPermissions;
use Versai\OneBlock\Tasks\PlaceBlockTask;
use Versai\OneBlock\Translator\Translator;
use Versai\OneBlock\Utils\Utils;

class EventListener implements Listener {

	public function onPlayerJoin(PlayerJoinEvent $event): void {
		Main::getInstance()->getSessionManager()->registerSession($event->getPlayer());
		Main::getInstance()->getBossBar()->addPlayer($event->getPlayer());
		$session = Main::getInstance()->getSessionManager()->getSession($event->getPlayer());
		if (Main::getInstance()->getDatabase()->playerInDatabase($event->getPlayer())) {
			$data = Main::getInstance()->getDatabase()->getPlayerData($event->getPlayer()->getXuid())[0];
			$session->setCoins((int)$data["coins"]);
			$session->setKills((int)$data["kills"]);
			$session->setDeaths((int)$data["deaths"]);
			$session->setBlocksBroken((int)$data["blocks_broken"]);
			$session->setBlocksPlaced((int)$data["blocks_placed"]);
		}
		if (Main::getInstance()->getDatabase()->playerHasIsland($event->getPlayer())) {
			if (Server::getInstance()->getWorldManager()->loadWorld("ob-".$event->getPlayer()->getXuid())) {
				$island = (new OneBlock(strtolower($event->getPlayer()->getXuid()), Server::getInstance()->getWorldManager()->getWorldByName("ob-".$event->getPlayer()->getXuid())));
				$session->setIsland($island);
				if (Main::getInstance()->getIslandManager()->getIslandByXuid($event->getPlayer()->getXuid())) { // island is already managed
					return;
				}
				Main::getInstance()->getIslandManager()->addIsland($island);
			}
		}
	}

	public function onPlayerExit(PlayerQuitEvent $event): void {
		$session = Main::getInstance()->getSessionManager()->getSession($event->getPlayer());
		if (!$session) {
			return;
		}
		if ($session->hasIsland()) {
			if ($session->getIsland()->getWorld()->getPlayers() != []) { // someone is on the island
				return;
			}
			Main::getInstance()->getIslandManager()->removeIsland($session->getIsland());
		}
		Main::getInstance()->getDatabase()->updatePlayer($session);
		Main::getInstance()->getDatabase()->updatePlayerIsland($session);
		Main::getInstance()->getSessionManager()->unregisterSession($event->getPlayer());
		Main::getInstance()->getBossBar()->removePlayer($event->getPlayer());
	}

	public function onBlockBreak(BlockBreakEvent $event): void {
		$player = $event->getPlayer();
		$session = Main::getInstance()->getSessionManager()->getSession($player);
		$block = $event->getBlock();
		$position = $block->getPosition();

		if (!str_starts_with($position->getWorld()->getFolderName(), "ob-")) {
			return;
		}

		# If the world is a OneBlock world
		if (str_starts_with($position->getWorld()->getFolderName(), "ob-")) {
			# Get island data
			# Using getByNameOffline() to provide support even if the owner is NOT online
			$island = Main::getInstance()->getIslandManager()->getIslandByXuid(str_replace("ob-", "", $position->getWorld()->getFolderName()));

			if (!$island) {
				$player->sendMessage(Translator::translate("errors.general", ["ISLND_NOT_MNGD"]));
				$event->cancel();
				return;
			}

			$isOwner = $player->getXuid() == $island->getOwner();

			$permissions = $island->getMemberPermissions($player->getXuid());

			$members = $island->getMembers();
			if ($position->equals(new Vector3(256, 64, 256))) {

				# If player is not in island members and is not owner

				if (!$permissions && !$isOwner) {
					$event->cancel();
					$player->sendMessage(Translator::translate("island.permissions.not_member"));
					return;
				}

				# If player is in the members list, and not the owner

				if ($permissions && !$isOwner) {
					# If member does not have permission to break the oneblock
					if (!in_array(OneBlockPermissions::BREAK_ONE_BLOCK, $permissions)) {
						$event->cancel();
						$player->sendMessage(Translator::translate("island.permissions.invalid"));
						return;
					}
				}

				# If member does not have the permission to break one blocks and is not the owner

				/*if (!in_array(OneBlockPermissions::BREAK_ONE_BLOCK, $members[$player->getXuid()]) && !$isOwner) {
					$event->cancel();
					$player->sendMessage(Translator::translate("island.permissions.invalid"));
					return;
				}*/
				foreach($event->getDrops() as $item) {
					if($player->getInventory()->canAddItem($item)) {
						$player->getInventory()->addItem($item);
					}
				}
				$event->setDrops([]);
				$session->addBlocksBroken(1);
				if (!$island->isMaxLevel()) {
					$island->addBlockBreak();
				}
				$blockString = Utils::getRandomBlockFromConfig($island->getLevel());
				$block = Utils::translateStringToBlock($blockString);
				Main::getInstance()->getScheduler()->scheduleDelayedTask(new PlaceBlockTask($block, $position), 0);
				return;
			}

			if (!$permissions) {
				if (!$isOwner) {
					$event->cancel();
					$player->sendMessage(Translator::translate("island.permissions.not_member"));
					return;
				}
				$permissions = []; // stop erroring
			}

			if (in_array(OneBlockPermissions::BREAK_BLOCKS, $permissions) || $isOwner) {

				foreach($event->getDrops() as $item) {
					if($player->getInventory()->canAddItem($item)) {
						$player->getInventory()->addItem($item);
					}
				}
				$event->setDrops([]);
				$session->addBlocksBroken(1);
				return;
			}

			$event->cancel();
		}
	}

	public function onPlaceBlock(BlockPlaceEvent $event): void {
		$player = $event->getPlayer();

		$session = Main::getInstance()->getSessionManager()->getSession($player);

		if (!$session) {
			$player->sendMessage(Translator::translate("errors.session.not_found"));
			$event->cancel();
			return;
		}

		if (!$session->isOnIsland() || !Main::getInstance()->getServer()->isOp($player->getName())) {
			$event->cancel();
			return;
		}

		$island = Main::getInstance()->getIslandManager()->getIsland($player->getWorld());
		if (!$island) {
			$player->sendMessage(Translator::translate("errors.general", ["ISLND_NOT_MNGD"]));
			$event->cancel();
			return;
		}

		// check the players permissions
		if ($island->getOwner() == $player->getXuid()) { // is the island owner
			return;
		}

		if (!$island->getMemberPermissions($player->getXuid())) {//is member
			$player->sendMessage(Translator::translate("island.permissions.not_member"));
			$event->cancel();
			return;
		}

		if (!$island->memberHasPermission($player->getXuid(), OneBlockPermissions::PLACE_BLOCKS)) {
			$event->cancel();
			$player->sendMessage(Translator::translate("island.permissions.invalid"));
		}
	}

	// TODO:
	/*public function onIslandLevelUp(IslandLevelUpEvent $event) {
		$event->getIsland()->getPlayer()->sendTitle(Translator::translate("island.level_up.title"), Translator::translate("island.level_up.sub_title", [Main::getInstance()->getSessionManager()->getSession($event->getIsland()->getPlayer())->getIsland()->getLevel()]));
	}*/

	public function onIslandCreate(IslandCreateEvent $event): void {
		# Player session will (should) never be null
		Main::getInstance()->getDatabase()->updatePlayerIsland(Main::getInstance()->getSessionManager()->getSession($event->getPlayer()));
		Main::getInstance()->getIslandManager()->addIsland($event->getIsland());
	}

	public function onPlayerSell(SellEvent $event): void {
		$player = $event->getPlayer();
		$items = $event->getItems();
		$total = $event->getTotal();
		$config = Main::getInstance()->getConfig();
		if ((int)$config->getNested("whale.amount") > $total) {
			return;
		}
		$webhook = Main::getInstance()->getWhaleWebhook();
		$message = new Message();
		$formatted = [];
		foreach ($items as $item => $amt) {
			$formatted[] = "{$item} x{$amt}";
		}
		$content = str_replace(
			["{WHALE}", "{AMOUNT}", "{ITEMS}"],
			["**".$player->getName()."**", $total, join("\n", $formatted)],
			$config->getNested("discord.webhooks.whale.text_sell")
		);
		$embed = (new Embed())
			->setTitle($config->getNested("discord.webhooks.whale.title") ?? "Whale Detected")
			->setDescription($content);
		$message->addEmbed($embed);
		$webhook->send($message);
	}

	public function onPlayerInteract(PlayerInteractEvent $event): void {

		$player = $event->getPlayer();

		$block = $event->getBlock();

		$session = Main::getInstance()->getSessionManager()->getSession($player);

		if (!$session) {
			$player->sendMessage(Translator::translate("errors.session.not_found"));
			$event->cancel();
			return;
		}

		if (!$session->isOnIsland()) {
			$player->sendMessage(Translator::translate("errors.island.none"));
			$event->cancel();
			return;
		}

		$island = Main::getInstance()->getIslandManager()->getIsland($player->getWorld());

		if (!$island) {
			$player->sendMessage(Translator::translate("errors.general", ["ISLND_NOT_MNGD"]));
			$event->cancel();
			return;
		}

		if ($island->getOwner() == $player->getXuid()) {
			return;
		}

		if ($block instanceof Chest || $block instanceof TChest) { // if it is a chest block or tile
			if (!$island->memberHasPermission($player->getXuid(), OneBlockPermissions::OPEN_CHESTS)) {
				$player->sendMessage(Translator::translate("island.permissions.invalid"));
				$event->cancel();
				return;
			}
		}

	}

	public function leavesDecay(LeavesDecayEvent $event): void {
		$event->cancel();
	}

	public function onDamage(EntityDamageEvent $event): void {
		if (!$event->getEntity() instanceof Player) {
			return;
		}
		if ($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
			$event->cancel();
			$pos = $event->getEntity()->getPosition();
			$event->getEntity()->teleport($pos->getWorld()->getSpawnLocation());
		}
	}

	public function onPVP(EntityDamageByEntityEvent $event) {
		$damager = $event->getDamager();

		if (!$damager instanceof Player) {
			return;
		}

		$session = Main::getInstance()->getSessionManager()->getSession($damager);

		if (!$session) {
			$event->cancel();
			return;
		}

		if ($session->isOnIsland()) {
			$island = Main::getInstance()->getIslandManager()->getIsland($damager->getWorld());

			if (!$island) {
				$damager->sendMessage(Translator::translate("island.none"));
				$event->cancel();
				return;
			}

			if ($island->getOwner() == $damager->getXuid()) { // Is owner
				return;
			}

			if (!$island->memberHasPermission($damager->getXuid(), OneBlockPermissions::DAMAGE_ENTITIES)) {
				$event->cancel();
			}
		}
	}

	public function onPlayerDropIsland(PlayerDropItemEvent $event) {
		$player = $event->getPlayer();

		$session = Main::getInstance()->getSessionManager()->getSession($player);

		if (!$session) {
			$player->sendMessage(Translator::translate("errors.session.none"));
			$event->cancel();
			return;
		}

		if ($session->isOnIsland()) {
			$island = Main::getInstance()->getIslandManager()->getIsland($player->getWorld());

			if (!$island) {
				$player->sendMessage(Translator::translate("island.none"));
				$event->cancel();
				return;
			}

			if ($island->getOwner() == $player->getXuid()) { // Is owner
				return;
			}

			if (!$island->memberHasPermission($player->getXuid(), OneBlockPermissions::DROP_ITEM)) {
				$event->cancel();
			}
		}
	}

	// Item Pick Up Event
	public function onItemPickup(EntityItemPickupEvent $event) {
		$player = $event->getEntity();
		if (!$player instanceof Player) {
			return;
		}
		$session = Main::getInstance()->getSessionManager()->getSession($player);

		if (!$session) {
			return;
		}

		if ($session->isOnIsland()) {
			$island = Main::getInstance()->getIslandManager()->getIsland($player->getWorld());

			if (!$island) {
				$player->sendMessage(Translator::translate("island.none"));
				$event->cancel();
				return;
			}

			if ($island->getOwner() == $player->getXuid()) { // Is owner
				return;
			}

			if (!$island->memberHasPermission($player->getXuid(), OneBlockPermissions::PICKUP_ITEMS)) {
				$event->cancel();
			}
		}
	}

	public function onHungerDecay(PlayerExhaustEvent $event): void {
		$event->cancel();
	}

	public function FallingBlockSpawn(EntitySpawnEvent $event): void {
		$entity = $event->getEntity();
		if ($entity instanceof FallingBlock) {
			$block = $entity->getBlock();
			if (
				($block instanceof Sand || $block instanceof Gravel) &&
				$block->getPosition()->equals(new Vector3(256, 64, 256))
			) {
				$entity->flagForDespawn();
				$entity->getPosition()->getWorld()->setBlock($entity->getPosition(), $entity->getBlock(), false);
			}
		}
	}
}

