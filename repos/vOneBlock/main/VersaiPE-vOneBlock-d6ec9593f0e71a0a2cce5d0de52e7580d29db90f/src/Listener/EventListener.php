<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Listener;

use pocketmine\block\Gravel;
use pocketmine\block\Sand;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\object\FallingBlock;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
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

	public function onPlayerJoin(PlayerJoinEvent $ev) {
		Main::getInstance()->getSessionManager()->registerSession($ev->getPlayer());
		Main::getInstance()->getBossBar()->addPlayer($ev->getPlayer());
		$session = Main::getInstance()->getSessionManager()->getSession($ev->getPlayer());
		if (Main::getInstance()->getDatabase()->playerInDatabase($ev->getPlayer())) {
			$data = Main::getInstance()->getDatabase()->getPlayerData($ev->getPlayer()->getXuid())[0];
			$session->setCoins((int)$data["coins"]);
			$session->setKills((int)$data["kills"]);
			$session->setDeaths((int)$data["deaths"]);
			$session->setBlocksBroken((int)$data["blocks_broken"]);
			$session->setBlocksPlaced((int)$data["blocks_placed"]);
		}
		if (Main::getInstance()->getDatabase()->playerHasIsland($ev->getPlayer())) {
			if (Server::getInstance()->getWorldManager()->loadWorld("ob-".$ev->getPlayer()->getXuid())) {
				$island = (new OneBlock(strtolower($ev->getPlayer()->getXuid()), Server::getInstance()->getWorldManager()->getWorldByName("ob-".$ev->getPlayer()->getXuid())));
				$session->setIsland($island);
				Main::getInstance()->getIslandManager()->addIsland($island);
			}
		}
	}

	public function onPlayerExit(PlayerQuitEvent $ev) {
		$session = Main::getInstance()->getSessionManager()->getSession($ev->getPlayer());
		if (!$session) {
			return;
		}
		if ($session->hasIsland()) {
			Main::getInstance()->getIslandManager()->removeIsland($session->getIsland());
		}
		Main::getInstance()->getDatabase()->updatePlayer($session);
		Main::getInstance()->getDatabase()->updatePlayerIsland($session);
		Main::getInstance()->getSessionManager()->unregisterSession($ev->getPlayer());
		Main::getInstance()->getBossBar()->removePlayer($ev->getPlayer());
	}

	public function onBlockBreak(BlockBreakEvent $ev) {
		$player = $ev->getPlayer();
		$session = Main::getInstance()->getSessionManager()->getSession($player);
		$block = $ev->getBlock();
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
				$ev->cancel();
				return;
			}

			$isOwner = $player->getXuid() == $island->getOwner();

			$members = $island->getMembers();
			if ($position->equals(new Vector3(256, 64, 256))) {

				# If player is not in island members and is not owner

				if (!in_array($player->getXuid(), $members) && !$isOwner) {
					$ev->cancel();
					$player->sendMessage(Translator::translate("island.permissions.invalid"));
					return;
				}

				# If player is in the members list, and not the owner

				if (in_array($player->getXuid(), $members) && !$isOwner) {
					# If member does not have permission to break the oneblock
					if (!in_array(OneBlockPermissions::BREAK_ONE_BLOCK, $members[$player->getXuid()])) {
						$ev->cancel();
						$player->sendMessage(Translator::translate("island.permissions.invalid"));
						return;
					}
				}

				# If member does not have the permission to break one blocks and is not the owner

				/*if (!in_array(OneBlockPermissions::BREAK_ONE_BLOCK, $members[$player->getXuid()]) && !$isOwner) {
					$ev->cancel();
					$player->sendMessage(Translator::translate("island.permissions.invalid"));
					return;
				}*/
				foreach($ev->getDrops() as $item) {
					if($player->getInventory()->canAddItem($item)) {
						$player->getInventory()->addItem($item);
					}
				}
				$ev->setDrops([]);
				$session->addBlocksBroken(1);
				// TODO: getIsland from other players session
				if (!$island->isMaxLevel()) {
					$island->addBlockBreak();
				}
				$blockString = Utils::getRandomBlockFromConfig($session->getIsland()->getLevel());
				$block = Utils::translateStringToBlock($blockString);
				Main::getInstance()->getScheduler()->scheduleDelayedTask(new PlaceBlockTask($block, $position), 0);
			}
		}
	}

	// TODO:
	/*public function onIslandLevelUp(IslandLevelUpEvent $ev) {
		$ev->getIsland()->getPlayer()->sendTitle(Translator::translate("island.level_up.title"), Translator::translate("island.level_up.sub_title", [Main::getInstance()->getSessionManager()->getSession($ev->getIsland()->getPlayer())->getIsland()->getLevel()]));
	}*/

	public function onIslandCreate(IslandCreateEvent $ev) {
		# Player session will (should) never be null
		Main::getInstance()->getDatabase()->updatePlayerIsland(Main::getInstance()->getSessionManager()->getSession($ev->getPlayer()));
		Main::getInstance()->getIslandManager()->addIsland($ev->getIsland());
	}

	public function onPlayerSell(SellEvent $ev) {
		$player = $ev->getPlayer();
		$items = $ev->getItems();
		$total = $ev->getTotal();
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

	public function leavesDecay(LeavesDecayEvent $ev) {
		$ev->cancel();
	}

	public function onDamage(EntityDamageEvent $ev) {
		if (!$ev->getEntity() instanceof Player) {
			return;
		}
		if ($ev->getCause() === EntityDamageEvent::CAUSE_VOID) {
			$ev->cancel();
			$pos = $ev->getEntity()->getPosition();
			$ev->getEntity()->teleport($pos->getWorld()->getSpawnLocation());
		}
	}

	public function onHungerDecay(PlayerExhaustEvent $ev) {
		$ev->cancel();
	}

	public function FallingBlockSpawn(EntitySpawnEvent $ev) {
		$entity = $ev->getEntity();
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

