<?php

namespace ethaniccc\VAC\detection;

use ethaniccc\VAC\data\ACData;
use ethaniccc\VAC\tasks\CacheLogTask;
use ethaniccc\VAC\tasks\KickTask;
use ethaniccc\VAC\VAC;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class Detection {

	protected float $violations = 0.0;
	protected float $buffer = 0.0;
	protected int $lastViolationTick = 0;
	protected float $lastBroadcastTime = 0.0;

	public function __construct(
		public ACData $data,
		public string $category,
		public string $subCategory,
		public string $description,
		public bool $isExperimental = false
	) {}

	public function getCategory(): string {
		return $this->category;
	}

	public function getSubCategory(): string {
		return $this->subCategory;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function isExperimental(): bool {
		return $this->isExperimental;
	}

	public function getSettings(): array {
		return VAC::getInstance()->getConfig()->getNested("detections.{$this->getCategory()}.{$this->getSubCategory()}", []);
	}

	public function getOption($option, $default) {
		return $this->getSettings()[$option] ?? $default;
	}

	public function getViolations(): float {
		return $this->violations;
	}

	public function getData(): ACData {
		return $this->data;
	}

	public function isEnabled(): bool {
		return $this->getOption("enabled", false);
	}

	public function getPunishmentType(): string {
		return $this->getOption("punishment_type", "none");
	}

	public function getCodename(): string {
		return $this->getOption("codename", "???");
	}

	public function getMaxViolations(): int {
		return $this->getOption("max_vl", 15);
	}

	public function buff(float $buff = 1, float $max = 15): float {
		$this->buffer += $buff;
		if ($this->buffer < 0) {
			$this->buffer = 0;
		} elseif ($this->buffer > $max) {
			$this->buffer = $max;
		}
		return $this->buffer;
	}

	abstract public function inbound(DataPacket $packet): void;

	public function destroy(): void {
		unset($this->data);
	}

	protected function flag(array $debug = [], float $violations = 1): void {
		$debug["ping"] = $this->getData()->getPlayer()->getPing();
		$this->violations += $violations;
		if (microtime(true) - $this->lastBroadcastTime > 0.1 && $violations > 0) {
			$message = str_replace([
				"{prefix}",
				"{name}",
				"{category}",
				"{sub_category}",
				"{violations}",
				"{debug}"
			], [
				VAC::getInstance()->getPrefix(),
				$this->getData()->getPlayer()->getName(),
				$this->getCategory(),
				$this->getSubCategory() . ($this->isExperimental() ? TextFormat::GRAY . "*" : ""),
				var_export(round($this->getViolations(), 1), true),
				$this->createDebugData($debug)
			], VAC::getInstance()->getViolationMessage());
			if (VAC::getInstance()->hasWebhook()) {
				VAC::getInstance()->webhookThread->send(TextFormat::clean($message));
			}
			Server::getInstance()->broadcastMessage($message, array_filter(Server::getInstance()->getOnlinePlayers(), function (Player $p): bool {
				if (!$p->hasPermission("ac.alerts")) {
					return false;
				}
				$inCooldown = VAC::getInstance()->getCooldownStatus($p->getName());
				if (!$inCooldown) {
					VAC::getInstance()->updateCooldownStatus($p->getName());
				}
				return !$inCooldown;
			}));
			Server::getInstance()->getLogger()->info($message);
			$this->lastBroadcastTime = microtime(true);
		}
		if ($this->violations >= $this->getMaxViolations()) {
			$type = $this->getPunishmentType();
			if ($this->getData()->overrideDetectionPunishmentStatus) {
				$type = "kick";
			}
			if ($type === "kick") {
				$this->getData()->terminate();
				$this->kick();
			}
		}
		$this->lastViolationTick = $this->getData()->currentTick;
	}

	protected function createDebugData(array $data): string {
		$debug = "";
		$times = count($data);
		foreach ($data as $k => $v) {
			$debug .= "$k=$v";
			$times--;
			if ($times !== 0) {
				$debug .= " ";
			}
		}
		return $debug;
	}

	protected function kick(): void {
		$logs = [];
		foreach ($this->getData()->detections as $detection) {
			if ($detection->getViolations() >= 1) {
				$logs[$detection->getCategory() . ":" . $detection->getSubCategory()] = $detection->getViolations();
			}
		}
		Server::getInstance()->getAsyncPool()->submitTask(new CacheLogTask(
			$this->getData()->getPlayer()->getName(), [
				"time" => date("m/d/y @ g:i a"),
				"logs" => $logs,
				"final_reason" => $this->getCategory()
			]
		));
		$message = str_replace([
			"{prefix}",
			"{code}"
		], [
			VAC::getInstance()->getPrefix(),
			$this->getCodename()
		], VAC::getInstance()->getKickMessage());
		VAC::getInstance()->getScheduler()->scheduleDelayedTask(new KickTask($this->getData()->getPlayer(), $message), 0);
		$message = str_replace([
			"{prefix}",
			"{name}",
			"{category}",
			"{sub_category}"
		], [
			VAC::getInstance()->getPrefix(),
			$this->getData()->getPlayer()->getName(),
			$this->getCategory(),
			$this->getSubCategory()
		], VAC::getInstance()->getKickBroadcast());
		Server::getInstance()->broadcastMessage($message, array_filter(Server::getInstance()->getOnlinePlayers(), function (Player $p): bool {
			return $p->hasPermission("ac.alerts");
		}));
		Server::getInstance()->getLogger()->info($message);
	}

	protected function createViolationFromLastFlag(int $maxTicks): float {
		$diff = $this->getData()->currentTick - $this->lastViolationTick;
		return max((($maxTicks + min($diff, 1)) - $diff) / $maxTicks, 0);
	}

}