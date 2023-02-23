<?php

namespace ethaniccc\VAC\data\location;

use pocketmine\math\Vector3;

final class LocationMap {

	/** @var LocationData[] */
	public array $map = [];

	public function spawn(int $id, Vector3 $spawn, Vector3 $motion, bool $isPlayer): void {
		$this->map[$id] = new LocationData(0.3, 1.8, $id, $spawn, $motion, $isPlayer);
	}

	public function get(int $id): ?LocationData {
		return $this->map[$id] ?? null;
	}

	public function remove(int $id): void {
		unset($this->map[$id]);
	}

	public function tick(): void {
		foreach ($this->map as $data) {
			$data->tick();
		}
	}

}