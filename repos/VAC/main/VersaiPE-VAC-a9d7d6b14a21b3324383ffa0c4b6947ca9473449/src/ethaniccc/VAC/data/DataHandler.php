<?php

namespace ethaniccc\VAC\data;

use pocketmine\Player;

final class DataHandler {

	public static ?self $instance = null;

	public static function init(): void {
		self::$instance = new self();
	}

	public static function getInstance(): ?self {
		return self::$instance;
	}

	/** @var ACData[] */
	public array $data = [];

	public function add(Player $player): ACData {
		$data = new ACData($player);
		$this->data[spl_object_hash($player)] = $data;
		return $data;
	}

	public function get(Player $player): ?ACData {
		return $this->data[spl_object_hash($player)] ?? null;
	}

	public function all(): array {
		return $this->data;
	}

	public function remove(Player $player): void {
		($this->data[spl_object_hash($player)] ?? null)?->destroy();
		unset($this->data[spl_object_hash($player)]);
	}

}