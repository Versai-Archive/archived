<?php

namespace ethaniccc\VAC;

use ethaniccc\VAC\data\DataHandler;
use ethaniccc\VAC\protocol\PlayerAuthInputPacket;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayerMovementSettings;
use pocketmine\network\mcpe\protocol\types\PlayerMovementType;

final class PMListener implements Listener {

	public const OUTBOUND_PACKETS = [
		MoveActorAbsolutePacket::NETWORK_ID, MovePlayerPacket::NETWORK_ID,
		AddActorPacket::NETWORK_ID, RemoveActorPacket::NETWORK_ID, AddPlayerPacket::NETWORK_ID,
		SetActorDataPacket::NETWORK_ID
	];
	public static bool $isACPacket = false;

	public function getPacket(DataPacketReceiveEvent $event): void {
		$packet = $event->getPacket();
		$player = $event->getOrigin()->getPlayer();
		if ($player === null) {
			return;
		}

		$data = DataHandler::getInstance()->get($player->getNetworkSession()) ?? DataHandler::getInstance()->add($player->getNetworkSession());

		if ($data->isTerminated()) {
			return;
		}
		$data->inboundQueue[] = $packet;

		if ($packet instanceof PlayerAuthInputPacket) {
			$event->cancel();
			if ($packet->hasFlag(PlayerAuthInputFlags::START_SPRINTING)) {
				$pk = new PlayerActionPacket();
				$pk->actorRuntimeId = $player->getId();
				$pk->action = PlayerAction::START_SPRINT;
				$pk->x = $player->getPosition()->x;
				$pk->y = $player->getPosition()->y;
				$pk->z = $player->getPosition()->z;
				$pk->face = $player->getHorizontalFacing();
				$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
			}
			if ($packet->hasFlag(PlayerAuthInputFlags::STOP_SPRINTING)) {
				$pk = new PlayerActionPacket();
				$pk->actorRuntimeId = $player->getId();
				$pk->action = PlayerAction::STOP_SPRINT;
				$pk->x = $player->getPosition()->x;
				$pk->y = $player->getPosition()->y;
				$pk->z = $player->getPosition()->z;
				$pk->face = $player->getHorizontalFacing();
				$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
			}
			if ($packet->hasFlag(PlayerAuthInputFlags::START_SNEAKING)) {
				$pk = new PlayerActionPacket();
				$pk->actorRuntimeId = $player->getId();
				$pk->action = PlayerAction::START_SNEAK;
				$pk->x = $player->getPosition()->x;
				$pk->y = $player->getPosition()->y;
				$pk->z = $player->getPosition()->z;
				$pk->face = $player->getHorizontalFacing();
				$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
			}
			if ($packet->hasFlag(PlayerAuthInputFlags::STOP_SNEAKING)) {
				$pk = new PlayerActionPacket();
				$pk->actorRuntimeId = $player->getId();
				$pk->action = PlayerAction::STOP_SNEAK;
				$pk->x = $player->getPosition()->x;
				$pk->y = $player->getPosition()->y;
				$pk->z = $player->getPosition()->z;
				$pk->face = $player->getHorizontalFacing();
				$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
			}
			if ($packet->hasFlag(PlayerAuthInputFlags::START_JUMPING)) {
				$pk = new PlayerActionPacket();
				$pk->actorRuntimeId = $player->getId();
				$pk->action = PlayerAction::JUMP;
				$pk->x = $player->getPosition()->x;
				$pk->y = $player->getPosition()->y;
				$pk->z = $player->getPosition()->z;
				$pk->face = $player->getHorizontalFacing();
				$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
			}
			if ($packet->blockActions !== null) {
				foreach ($packet->blockActions as $action) {
					$pk = new PlayerActionPacket();
					$pk->actorRuntimeId = $player->getId();
					switch ($action->getActionType()) {
						case PlayerAction::START_BREAK:
							$pk->action = PlayerAction::START_BREAK;
							$pk->x = $action->blockPos->x;
							$pk->y = $action->blockPos->y;
							$pk->z = $action->blockPos->z;
							$pk->face = $player->getHorizontalFacing();
							$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
							break;
						case PlayerAction::CRACK_BREAK:
							$pk->action = PlayerAction::CRACK_BREAK;
							$pk->x = $action->blockPos->x;
							$pk->y = $action->blockPos->y;
							$pk->z = $action->blockPos->z;
							$pk->face = $player->getHorizontalFacing();
							$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
							break;
						case PlayerAction::ABORT_BREAK:
							$pk->action = PlayerAction::ABORT_BREAK;
							$pk->x = $action->blockPos->x;
							$pk->y = $action->blockPos->y;
							$pk->z = $action->blockPos->z;
							$pk->face = $player->getHorizontalFacing();
							$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
							break;
						case PlayerAction::STOP_BREAK:
							$pk->action = PlayerAction::STOP_BREAK;
							$position = $packet->getPosition()->subtract(0, 1.62, 0);
							$pk->x = $position->x;
							$pk->y = $position->y;
							$pk->z = $position->z;
							$pk->face = $player->getHorizontalFacing();
							$player->getNetworkSession()->getHandler()->handlePlayerAction($pk);
							break;
						case PlayerAction::PREDICT_DESTROY_BLOCK:
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
				$useBreakOn = $player->getWorld()->useBreakOn($packet->itemInteractionData->blockPos, $item, $player, true);
				if ($canInteract and $useBreakOn) {
					if ($player->isSurvival()) {
						if (!$item->equalsExact($oldItem) and $oldItem->equalsExact($player->getInventory()->getItemInHand())) {
							$player->getInventory()->setItemInHand($item);
						}
					}
				} else {
					$target = $player->getWorld()->getBlock($packet->itemInteractionData->blockPos);
					$blocks = $target->getAllSides();
					$blocks[] = $target;
					$player->getWorld()->createBlockUpdatePackets($blocks);

				}
			}

			if ($player->isConnected() && $player->isOnline()) {
				$pk = new MovePlayerPacket();
				$pk->actorRuntimeId = $player->getId();
				$pk->position = $packet->getPosition();
				$pk->yaw = $packet->getYaw();
				$pk->headYaw = $packet->getHeadYaw();
				$pk->pitch = $packet->getPitch();
				$pk->mode = MovePlayerPacket::MODE_NORMAL;
				$pk->onGround = true;
				$pk->tick = $packet->getTick();
				$player->getNetworkSession()->getHandler()->handleMovePlayer($pk);
			}
		}
	}

	public function sendPacket(DataPacketSendEvent $event): void {
		$targets = $event->getTargets();
		foreach ($targets as $target) {
			$data = DataHandler::getInstance()->get($target);
			if ($data === null) {
				continue;
			}

			$packets = $event->getPackets();
			foreach ($packets as $packet) {
				if ($packet instanceof StartGamePacket) {
					$packet->playerMovementSettings = new PlayerMovementSettings(
						PlayerMovementType::SERVER_AUTHORITATIVE_V1,
						0,
						false
					);
				}
				$data->outboundQueue[] = $packet;
			}
		}
	}

	public function leave(PlayerQuitEvent $event) {
		DataHandler::getInstance()->remove($event->getPlayer()->getNetworkSession());
	}

}