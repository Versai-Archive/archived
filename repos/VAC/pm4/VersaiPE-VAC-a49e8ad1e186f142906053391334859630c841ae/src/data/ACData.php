<?php

namespace ethaniccc\VAC\data;

use ethaniccc\VAC\data\attack\AttackData;
use ethaniccc\VAC\data\click\ClickData;
use ethaniccc\VAC\data\latency\NetworkStackLatencyHandler;
use ethaniccc\VAC\data\location\LocationMap;
use ethaniccc\VAC\detection\autoclicker\AutoclickerA;
use ethaniccc\VAC\detection\autoclicker\AutoclickerB;
use ethaniccc\VAC\detection\Detection;
use ethaniccc\VAC\detection\killaura\KillauraA;
use ethaniccc\VAC\detection\reach\ReachA;
use ethaniccc\VAC\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\NetworkSession;
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
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\Server;

final class ACData {

	public bool $terminated = false;
	public bool $overrideDetectionPunishmentStatus = false;

	public int $currentTick = 0;
	public int $lastLocationACKTimestamp = -1;

	public float $yaw = 0;
	public float $pitch = 0;

	/** @var Detection[] */
	public array $detections = [];

	public ClickData $clickData;
	public AttackData $attackData;

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

	private NetworkSession $session;

	public function __construct(NetworkSession $session) {
		$this->session = $session;

		$this->clickData = new ClickData();
		$this->attackData = new AttackData();

		$this->locMap = new LocationMap();

		$this->latencyHandler = new NetworkStackLatencyHandler($this);

		$this->detections = [
			new AutoclickerA($this),
			new AutoclickerB($this),

			new KillauraA($this),

			new ReachA($this),
		];
	}

	public function inbound(DataPacket $packet): void {
		if ($this->isTerminated()) {
			return;
		}
		if ($packet instanceof PlayerAuthInputPacket) {
			++$this->currentTick;
			$this->inputMode = $packet->getInputMode();
			$this->locMap->tick();
			$this->yaw = $packet->getYaw();
			$this->pitch = $packet->getPitch();
		} elseif ($packet instanceof InventoryTransactionPacket) {
			$trData = $packet->trData;
			if ($trData instanceof UseItemOnEntityTransactionData && $trData->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK) {
				$this->clickData->add($this->currentTick);
				$this->attackData->set($trData->getActorRuntimeId(), $this->currentTick, $trData->getPlayerPosition());
			}
		} elseif ($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
			$this->clickData->add($this->currentTick);
		} elseif ($packet instanceof LoginPacket) {
			$chain = $packet->chainDataJwt->chain;
			try {
				$username = JwtUtils::parse($chain[array_key_last($chain)])[1]['extraData']['displayName'];
			} catch (JwtException $e) {
				throw PacketHandlingException::wrap($e);
			}
			if (!Player::isValidUserName($username)) {
				return;
			}
			try {
				$clientData = JwtUtils::parse($packet->clientDataJwt)[1];
			} catch (JwtException $e) {
				throw PacketHandlingException::wrap($e);
			}
			$this->playerOS = $clientData["DeviceOS"];
			if ($this->playerOS === DeviceOS::ANDROID) {
				$deviceModel = (string)($clientData["DeviceModel"]);
				$name = explode(" ", $deviceModel);
				if (isset($name[0]) && strtoupper($name[0]) !== $name[0]) {
					// the player is potentially using toolbox
					Server::getInstance()->getLogger()->info("{$username} is potentially using Toolbox");
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

	public function isTerminated(): bool {
		return $this->terminated;
	}

	public function outbound(DataPacket $packet): void {
		if (($packet instanceof MovePlayerPacket || $packet instanceof MoveActorAbsolutePacket) && $packet->actorRuntimeId !== $this->getPlayer()->getId()) {
			$this->queuedLocations[] = $packet;
		} elseif ($packet instanceof SetActorDataPacket && $packet->actorRuntimeId === $this->getPlayer()->getId()) {
			$this->latencyHandler->send(function () use ($packet): void {
				$locDat = $this->locMap->get($packet->actorRuntimeId);
				if ($locDat === null) {
					return;
				}
				$width = isset($packet->metadata[53]) ? ($packet->metadata[53][1] / 2) : $locDat->getWidth();
				$height = $packet->metadata[54][1] ?? $locDat->getHeight();
				$locDat->setWidth($width);
				$locDat->setHeight($height);
			});
		} elseif ($packet instanceof AddActorPacket || $packet instanceof AddPlayerPacket) {
			$this->latencyHandler->send(function () use ($packet): void {
				$this->locMap->spawn($packet->actorRuntimeId, $packet->position->subtract(0, ($packet instanceof AddPlayerPacket ? 1.62 : 0), 0), $packet->motion, $packet instanceof AddPlayerPacket);
			});
		} elseif ($packet instanceof RemoveActorPacket) {
			$this->latencyHandler->send(function () use ($packet): void {
				$this->locMap->remove($packet->actorUniqueId);
			});
		}
	}

	public function getPlayer(): Player {
		return $this->session->getPlayer();
	}

	public function terminate(): void {
		$this->terminated = true;
	}

	public function destroy(): void {
		foreach ($this->detections as $detection) {
			$detection->destroy();
		}
		$this->latencyHandler->destroy();
	}

}