<?php

namespace ethaniccc\VAC;

use ethaniccc\VAC\data\DataHandler;
use ethaniccc\VAC\mcprotocol\InputConstants;
use ethaniccc\VAC\mcprotocol\v428\PlayerAuthInputPacket;
use ethaniccc\VAC\mcprotocol\v428\PlayerBlockAction;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\PlayerMovementSettings;
use pocketmine\network\mcpe\protocol\types\PlayerMovementType;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\tile\Spawnable;

final class PMListener implements Listener {

	public static bool $isACPacket = false;

	public const OUTBOUND_PACKETS = [
		MoveActorAbsolutePacket::NETWORK_ID, MovePlayerPacket::NETWORK_ID,
		AddActorPacket::NETWORK_ID, RemoveActorPacket::NETWORK_ID, AddPlayerPacket::NETWORK_ID,
		SetActorDataPacket::NETWORK_ID, SetActorMotionPacket::NETWORK_ID
	];

	public function getPacket(DataPacketReceiveEvent $event): void {
		$packet = $event->getPacket();
		$player = $event->getPlayer();

		if (!$player->isClosed() && !$packet instanceof BatchPacket) {
			$data = DataHandler::getInstance()->get($player) ?? DataHandler::getInstance()->add($player);
			$data->inboundQueue[] = $packet;
		}

		if ($packet instanceof PlayerAuthInputPacket) {
			$event->setCancelled();
			if (InputConstants::hasFlag($packet, InputConstants::START_SPRINTING)) {
				$pk = new PlayerActionPacket();
				$pk->entityRuntimeId = $player->getId();
				$pk->action = PlayerActionPacket::ACTION_START_SPRINT;
				$pk->x = $player->x;
				$pk->y = $player->y;
				$pk->z = $player->z;
				$pk->face = $player->getDirection();
				$player->handlePlayerAction($pk);
			}
			if (InputConstants::hasFlag($packet, InputConstants::STOP_SPRINTING)) {
				$pk = new PlayerActionPacket();
				$pk->entityRuntimeId = $player->getId();
				$pk->action = PlayerActionPacket::ACTION_STOP_SPRINT;
				$pk->x = $player->x;
				$pk->y = $player->y;
				$pk->z = $player->z;
				$pk->face = $player->getDirection();
				$player->handlePlayerAction($pk);
			}
			if (InputConstants::hasFlag($packet, InputConstants::START_SNEAKING)) {
				$pk = new PlayerActionPacket();
				$pk->entityRuntimeId = $player->getId();
				$pk->action = PlayerActionPacket::ACTION_START_SNEAK;
				$pk->x = $player->x;
				$pk->y = $player->y;
				$pk->z = $player->z;
				$pk->face = $player->getDirection();
				$player->handlePlayerAction($pk);
			}
			if (InputConstants::hasFlag($packet, InputConstants::STOP_SNEAKING)) {
				$pk = new PlayerActionPacket();
				$pk->entityRuntimeId = $player->getId();
				$pk->action = PlayerActionPacket::ACTION_STOP_SNEAK;
				$pk->x = $player->x;
				$pk->y = $player->y;
				$pk->z = $player->z;
				$pk->face = $player->getDirection();
				$player->handlePlayerAction($pk);
			}
			if (InputConstants::hasFlag($packet, InputConstants::START_JUMPING)) {
				$pk = new PlayerActionPacket();
				$pk->entityRuntimeId = $player->getId();
				$pk->action = PlayerActionPacket::ACTION_JUMP;
				$pk->x = $player->x;
				$pk->y = $player->y;
				$pk->z = $player->z;
				$pk->face = $player->getDirection();
				$player->handlePlayerAction($pk);
			}
			if ($packet->blockActions !== null) {
				foreach ($packet->blockActions as $action) {
					$pk = new PlayerActionPacket();
					$pk->entityRuntimeId = $player->getId();
					switch ($action->actionType) {
						case PlayerBlockAction::START_BREAK:
							$pk->action = PlayerActionPacket::ACTION_START_BREAK;
							$pk->x = $action->blockPos->x;
							$pk->y = $action->blockPos->y;
							$pk->z = $action->blockPos->z;
							$pk->face = $player->getDirection();
							$player->handlePlayerAction($pk);
							break;
						case PlayerBlockAction::CONTINUE:
						case PlayerBlockAction::CRACK_BREAK:
							$pk->action = PlayerActionPacket::ACTION_CRACK_BREAK;
							$pk->x = $action->blockPos->x;
							$pk->y = $action->blockPos->y;
							$pk->z = $action->blockPos->z;
							$pk->face = $player->getDirection();
							$player->handlePlayerAction($pk);
							break;
						case PlayerBlockAction::ABORT_BREAK:
							$pk->action = PlayerActionPacket::ACTION_ABORT_BREAK;
							$pk->x = $action->blockPos->x;
							$pk->y = $action->blockPos->y;
							$pk->z = $action->blockPos->z;
							$pk->face = $player->getDirection();
							$player->handlePlayerAction($pk);
							break;
						case PlayerBlockAction::STOP_BREAK:
							$pk->action = PlayerActionPacket::ACTION_STOP_BREAK;
							$position = $packet->getPosition()->subtract(0, 1.62);
							$pk->x = $position->x;
							$pk->y = $position->y;
							$pk->z = $position->z;
							$pk->face = $player->getDirection();
							$player->handlePlayerAction($pk);
							break;
						case PlayerBlockAction::PREDICT_DESTROY:
							break;
					}
				}
			}

			if ($packet->itemInteractionData !== null) {
				// maybe if :microjang: didn't make the block breaking server-side option redundant, I wouldn't be doing this... you know who to blame !
				// hahaha... skidding PMMP go brrrt
				$player->doCloseInventory();
				$item = $player->getInventory()->getItemInHand();
				$oldItem = clone $item;
				$canInteract = $player->canInteract($packet->itemInteractionData->blockPos->add(0.5, 0.5, 0.5), $player->isCreative() ? 13 : 7);
				$useBreakOn = $player->getLevel()->useBreakOn($packet->itemInteractionData->blockPos, $item, $player, true);
				if ($canInteract and $useBreakOn) {
					if ($player->isSurvival()) {
						if (!$item->equalsExact($oldItem) and $oldItem->equalsExact($player->getInventory()->getItemInHand())) {
							$player->getInventory()->setItemInHand($item);
							$player->getInventory()->sendHeldItem($player->getViewers());
						}
					}
				} else {
					$player->getInventory()->sendContents($player);
					$player->getInventory()->sendHeldItem($player);
					$target = $player->getLevel()->getBlock($packet->itemInteractionData->blockPos);
					$blocks = $target->getAllSides();
					$blocks[] = $target;
					$player->getLevel()->sendBlocks([$player], $blocks, UpdateBlockPacket::FLAG_ALL_PRIORITY);
					foreach ($blocks as $b) {
						$tile = $player->getLevel()->getTile($b);
						if ($tile instanceof Spawnable) {
							$tile->spawnTo($player);
						}
					}
				}
			}

			if ($player->isOnline()) {
				$pk = new MovePlayerPacket();
				$pk->entityRuntimeId = $player->getId();
				$pk->position = $packet->getPosition();
				$pk->yaw = $packet->getYaw();
				$pk->headYaw = $packet->getHeadYaw();
				$pk->pitch = $packet->getPitch();
				$pk->mode = MovePlayerPacket::MODE_NORMAL;
				$pk->onGround = true;
				$pk->tick = $packet->getTick();
				$player->handleMovePlayer($pk);
			}
		}
	}

	public function sendPacket(DataPacketSendEvent $event): void {
		$packet = $event->getPacket();
		if ($packet instanceof StartGamePacket) {
			$packet->playerMovementSettings = new PlayerMovementSettings(
				PlayerMovementType::SERVER_AUTHORITATIVE_V1,
				0,
				false
			);
		} elseif ($packet instanceof BatchPacket) {
			if (self::$isACPacket) {
				self::$isACPacket = false;
				return;
			}
			$data = DataHandler::getInstance()->get($event->getPlayer());
			if ($data === null) {
				return;
			}
			if ($packet->getCompressionLevel() > 0) {
				/* Server::getInstance()->getAsyncPool()->submitTask(new DecompressBatchTask($packet, function (BatchPacket $result) use ($data): void {
					foreach ($result->getPackets() as $buff) {
						$pk = PacketPool::getPacket($buff);
						if (in_array($pk->pid(), self::OUTBOUND_PACKETS)) {
							var_dump(get_class($pk));
							$pk->decode();
							$data->outboundQueue[] = $pk;
						}
					}
					$result->setCompressionLevel(7);
					Server::getInstance()->getAsyncPool()->submitTask(new CompressBatchTask($result, $data->getPlayer()));
				}));
				$event->setCancelled(); */
				return;
			}
			$packet->decode();
			foreach ($packet->getPackets() as $buff) {
				$pk = PacketPool::getPacket($buff);
				if (in_array($pk->pid(), self::OUTBOUND_PACKETS)) {
					$pk->decode();
					$data->outboundQueue[] = $pk;
				}
			}
		}
	}

	public function leave(PlayerQuitEvent $event) {
		DataHandler::getInstance()->remove($event->getPlayer());
	}

}