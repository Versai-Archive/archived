<?php
declare(strict_types=1);

namespace ethaniccc\VAC;

use ethaniccc\VAC\data\DataHandler;
use ethaniccc\VAC\protocol\PlayerAuthInputPacket;
use ethaniccc\VAC\tasks\TickingTask;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class VAC extends PluginBase {

	use SingletonTrait;

	public array $cooldownTimes = [];
	public array $cooldown = [];

	public function onEnable(): void {
		self::setInstance($this);
		DataHandler::init();
		PacketPool::getInstance()->registerPacket(new PlayerAuthInputPacket());
		$this->getScheduler()->scheduleRepeatingTask(new TickingTask(), 1);
		$this->getServer()->getPluginManager()->registerEvents(new PMListener(), $this);
		$this->getServer()->getCommandMap()->register($this->getName(), new VACCommand());
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

	public function getCooldown(string $name): float {
		$cooldown = $this->cooldown[$name] ?? null;
		if ($cooldown === null) {
			$this->setCooldown($name, 2);
			$cooldown = 2;
		}
		return $cooldown;
	}

	public function setCooldown(string $name, float $cooldown): void {
		$this->cooldown[$name] = $cooldown;
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

}
