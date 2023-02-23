<?php

declare(strict_types=1);

namespace ethaniccc\VAC;

use ethaniccc\VAC\data\DataHandler;
use ethaniccc\VAC\mcprotocol\v428\PlayerAuthInputPacket;
use ethaniccc\VAC\tasks\TickingTask;
use ethaniccc\VAC\thread\WebhookThread;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\PluginBase;


class VAC extends PluginBase {

	public static self $instance;

	public static function getInstance(): self {
		return self::$instance;
	}

	public array $cooldownTimes = [];
	public array $cooldown = [];
	public WebhookThread $webhookThread;

	public function onEnable() {
		self::$instance = $this;
		DataHandler::init();
		PacketPool::registerPacket(new PlayerAuthInputPacket());
		$this->getScheduler()->scheduleRepeatingTask(new TickingTask(), 1);
		$this->getServer()->getPluginManager()->registerEvents(new PMListener(), $this);
		$this->getServer()->getCommandMap()->register($this->getName(), new VACCommand());
		if ($this->hasWebhook()) {
			($this->webhookThread = new WebhookThread($this->getWebhookLink()))->start();
			$this->webhookThread->send("VAC has been enabled");
		}
	}

	public function onDisable() {
		$this->webhookThread->quit();
	}

	public function setCooldown(string $name, float $cooldown): void {
		$this->cooldown[$name] = $cooldown;
	}

	public function getCooldown(string $name): float {
		$cooldown = $this->cooldown[$name] ?? null;
		if ($cooldown === null) {
			$this->setCooldown($name, 2);
			$cooldown = 2;
		}
		return $cooldown;
	}

	public function getCooldownStatus(string $name): bool {
		if (!isset($this->cooldownTimes[$name])) {
			$this->updateCooldownStatus($name);
			return false;
		} else {
			$delta = microtime(true) - $this->cooldownTimes[$name];
			return $delta <= $this->getCooldown($name);
		}
	}

	public function updateCooldownStatus(string $name): void {
		$this->cooldownTimes[$name] = microtime(true) + $this->getCooldown($name);
	}

	public function getPrefix(): string {
		return $this->getConfig()->get("prefix", "§l§7[§bV§fA§bC§7]§r");
	}

	public function getViolationMessage(): string {
		return $this->getConfig()->get("violation_message", "§b{name} §fflagged §6{category} §e(§d{sub_category}§e) §7(§cx{violations}§7) §7[{debug}]");
	}

	public function getKickMessage(): string {
		return $this->getConfig()->get("kick_message", "{prefix} You were kicked due to §c§lpotential§r §bunfair advantage§r [code={code}]\nMake a ticket on discord.versai.pro if this issue persists");
	}

	public function getKickBroadcast(): string {
		return $this->getConfig()->get("kick_broadcast", "{prefix} §b{name} §rwas kicked from the server for flagging §b{category} §7(§b{sub_category}§7)");
	}

	public function hasWebhook(): bool {
		return $this->getConfig()->get("webhook_link", "none") !== "none";
	}

	public function getWebhookLink(): string {
		return $this->getConfig()->get("webhook_link", "none");
	}

}
