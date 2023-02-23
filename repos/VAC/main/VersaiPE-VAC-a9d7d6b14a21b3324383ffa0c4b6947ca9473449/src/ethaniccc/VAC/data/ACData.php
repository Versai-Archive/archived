<?php

namespace ethaniccc\VAC\data;

use ethaniccc\VAC\data\attack\AttackData;
use ethaniccc\VAC\data\click\ClickData;
use ethaniccc\VAC\data\latency\NetworkStackLatencyHandler;
use ethaniccc\VAC\data\location\LocationMap;
use ethaniccc\VAC\data\movement\MovementData;
use ethaniccc\VAC\detection\autoclicker\AutoclickerA;
use ethaniccc\VAC\detection\autoclicker\AutoclickerB;
use ethaniccc\VAC\detection\Detection;
use ethaniccc\VAC\detection\killaura\KillauraA;
use ethaniccc\VAC\detection\reach\ReachA;
use ethaniccc\VAC\detection\velocity\VelocityA;
use ethaniccc\VAC\mcprotocol\InputConstants;
use ethaniccc\VAC\mcprotocol\v428\PlayerAuthInputPacket;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\Player;
use pocketmine\Server;

final class ACData {

	public bool $terminated = false;
	public bool $overrideDetectionPunishmentStatus = false;

	public int $currentTick = 0;
	public int $lastLocationACKTimestamp = -1;

	/** @var Detection[] */
	public array $detections = [];

	public ClickData $clickData;
	public AttackData $attackData;
	public MovementData $movementData;

	public LocationMap $locMap;

	public NetworkStackLatencyHandler $latencyHandler;

	/** @var MovePlayerPacket[]|MoveActorAbsolutePacket[] */
	public array $queuedLocations = [];
	/** @var DataPacket[] */
	public array $inboundQueue = [];
	/** @var DataPacket[] */
	public array $outboundQueue = [];

	public int $playerOS = DeviceOS::UNKNOWN;
	public int $inputMode = -1;

	public function __construct(public Player $player) {
		$this->clickData = new ClickData();
		$this->attackData = new AttackData();
		$this->movementData = new MovementData();

		$this->locMap = new LocationMap();

		$this->latencyHandler = new NetworkStackLatencyHandler($this);

		$this->detections = [
			new AutoclickerA($this),
			new AutoclickerB($this),

			new KillauraA($this),

			new ReachA($this),

			new VelocityA($this),
		];
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function inbound(DataPacket $packet): void {
		if ($this->isTerminated()) {
			return;
		}
		if ($packet instanceof PlayerAuthInputPacket) {
			$this->currentTick++;
			$this->inputMode = $packet->getInputMode();

			$this->locMap->tick();
			
			$this->movementData->lastYaw = $this->movementData->yaw;
			$this->movementData->yaw = $packet->getYaw();

			$this->movementData->lastPitch = $this->movementData->pitch;
			$this->movementData->pitch = $packet->getPitch();

			$this->movementData->setForward($packet->getMoveVecZ() * 0.98);
			$this->movementData->setStrafe($packet->getMoveVecX() * 0.98);

			$this->movementData->lastClientPredictionDelta = $packet->getDelta();
			if (InputConstants::hasFlag($packet, InputConstants::START_JUMPING)) {
				$this->movementData->lastClientPredictionDelta->y = $this->getPlayer()->getJumpVelocity();
			}
			$this->movementData->setCurrentPos($packet->getPosition()->subtract(0, 1.62));

			/* $surrounding = LevelUtils::getCollisionBlocks(
				$this->getPlayer()->getLevel(),
				AABB::fromPosition($this->movementData->currentPos)->expand(0.2, 0.2, 0.2),
				false
			);
			$cobweb = false;
			$liquid = false;
			$climb = false;
			foreach ($surrounding as $block) {
				if ($block instanceof Cobweb) {
					$cobweb = true;
				} elseif ($block instanceof Liquid) {
					$liquid = true;
				} elseif ($block->canClimb()) {
					$climb = true;
				}
			}

			$this->movementData->hasBlockAbove = (
				$this->movementData->lastClientPredictionDelta->y > 0.005
				&& abs($this->movementData->lastClientPredictionDelta->y - $this->movementData->currentDelta->y) > 0.001
				&& $this->getPlayer()->getLevel()->getBlock($this->movementData->currentPos->add(0, 2))->getId() !== 0
			);
			if ($cobweb) {
				$this->movementData->ticksSinceCobweb = 0;
			}
			if ($liquid) {
				$this->movementData->ticksSinceLiquid = 0;
			}
			if ($climb) {
				$this->movementData->ticksSinceClimb = 0;
			} */
		} elseif ($packet instanceof InventoryTransactionPacket) {
			$trData = $packet->trData;
			if ($trData instanceof UseItemOnEntityTransactionData && $trData->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK) {
				$this->clickData->add($this->currentTick);
				$this->attackData->set($trData->getEntityRuntimeId(), $this->currentTick, $trData->getPlayerPos());
			}
		} elseif ($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE) {
			$this->clickData->add($this->currentTick);
		} elseif ($packet instanceof LoginPacket) {
			$this->playerOS = $packet->clientData["DeviceOS"];
			if ($this->playerOS === DeviceOS::ANDROID) {
				$deviceModel = (string) ($packet->clientData["DeviceModel"]);
				$name = explode(" ", $deviceModel);
				if (isset($name[0]) && strtoupper($name[0]) !== $name[0]) {
					// the player is potentially using toolbox
					Server::getInstance()->getLogger()->info("{$packet->username} is potentially using Toolbox");
					$this->overrideDetectionPunishmentStatus = true;
				}
			}
		} elseif ($packet instanceof NetworkStackLatencyPacket) {
			$this->latencyHandler->execute($packet->timestamp);
		}
		foreach ($this->detections as $detection) {
			if ($detection->isEnabled()) {
				$detection->inbound($packet);
			}
		}
	}

	public function outbound(DataPacket $packet): void {
		if (($packet instanceof MovePlayerPacket || $packet instanceof MoveActorAbsolutePacket) && $packet->entityRuntimeId !== $this->getPlayer()->getId()) {
			$this->queuedLocations[] = $packet;
		} elseif ($packet instanceof SetActorDataPacket && $packet->entityRuntimeId !== $this->getPlayer()->getId()) {
			$this->latencyHandler->send(function () use ($packet): void {
				$locDat = $this->locMap->get($packet->entityRuntimeId);
				if ($locDat === null) {
					return;
				}
				$width = isset($packet->metadata[Entity::DATA_BOUNDING_BOX_WIDTH]) ? ($packet->metadata[Entity::DATA_BOUNDING_BOX_WIDTH][1] / 2) : $locDat->getWidth();
				$height = $packet->metadata[Entity::DATA_BOUNDING_BOX_HEIGHT][1] ?? $locDat->getHeight();
				$locDat->setWidth($width);
				$locDat->setHeight($height);
			});
		} elseif ($packet instanceof AddActorPacket || $packet instanceof AddPlayerPacket) {
			$this->latencyHandler->send(function () use ($packet): void {
				$this->locMap->spawn($packet->entityRuntimeId, $packet->position->subtract(0, ($packet instanceof AddPlayerPacket ? 1.62 : 0)), $packet->motion, $packet instanceof AddPlayerPacket);
			});
		} elseif ($packet instanceof RemoveActorPacket) {
			$this->latencyHandler->send(function () use ($packet): void {
				$this->locMap->remove($packet->entityUniqueId);
			});
		} elseif ($packet instanceof SetActorMotionPacket && $packet->entityRuntimeId === $this->getPlayer()->getId()) {
			/* $this->latencyHandler->send(function () use ($packet): void {
				if ($this->getPlayer()->isAlive()) {
					$this->movementData->setServerMotion($packet->motion);
				}
			}); */
		}
	}

	public function terminate(): void {
		$this->terminated = true;
	}

	public function isTerminated(): bool {
		return $this->terminated;
	}

	public function destroy(): void {
		foreach ($this->detections as $detection) {
			$detection->destroy();
		}
		$this->latencyHandler->destroy();
	}

}