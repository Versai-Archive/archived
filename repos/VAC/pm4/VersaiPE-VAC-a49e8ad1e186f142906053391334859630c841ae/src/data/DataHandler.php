<?php

namespace ethaniccc\VAC\data;

use pocketmine\network\mcpe\NetworkSession;

final class DataHandler {

	public static ?self $instance = null;
	/** @var ACData[] */
	public array $data = [];

	public static function init(): void {
		self::$instance = new self();
	}

	public static function getInstance(): ?self {
		return self::$instance;
	}

	public function add(NetworkSession $session): ACData {
		$data = new ACData($session);
		$this->data[spl_object_hash($session)] = $data;
		return $data;
	}

	public function get(NetworkSession $session): ?ACData {
		return $this->data[spl_object_hash($session)] ?? null;
	}

	public function all(): array {
		return $this->data;
	}

	public function remove(NetworkSession $session): void {
		($this->data[spl_object_hash($session)] ?? null)?->destroy();
		unset($this->data[spl_object_hash($session)]);
	}

}