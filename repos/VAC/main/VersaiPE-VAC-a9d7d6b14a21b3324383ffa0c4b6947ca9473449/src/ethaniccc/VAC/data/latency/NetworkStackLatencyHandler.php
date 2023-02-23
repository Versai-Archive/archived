<?php

namespace ethaniccc\VAC\data\latency;

use ethaniccc\VAC\data\ACData;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\types\DeviceOS;

final class NetworkStackLatencyHandler {

	public static function makePacket(): NetworkStackLatencyPacket {
		$packet = new NetworkStackLatencyPacket();
		$packet->timestamp = mt_rand(1, 100000000) * 1000;
		$packet->needResponse = true;
		return $packet;
	}

	public function __construct(
		public ACData $data
	) {}

	public array $map = [];
	public bool $isDestroyed = false;

	public function send(callable $run): void {
		if ($this->isDestroyed) {
			return;
		}
		$packet = self::makePacket();
		$this->add($run, $packet->timestamp);
		$this->data->getPlayer()->batchDataPacket($packet);
	}

	public function add(callable $run, int $timestamp): void {
		if ($this->isDestroyed) {
			return;
		}
		if ($this->data->playerOS === DeviceOS::PLAYSTATION) {
			$timestamp /= 1000;
		}
		$this->map[$timestamp] = $run;
	}

	public function execute(int $timestamp): void {
		if ($this->isDestroyed) {
			return;
		}
		$run = $this->map[$timestamp] ?? null;
		if ($run !== null) {
			($run)();
		}
		unset($this->map[$timestamp]);
	}

	public function destroy(): void {
		$this->isDestroyed = true;
		unset($this->data);
	}

}