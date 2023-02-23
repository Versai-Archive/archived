<?php

declare(strict_types=1);

namespace Versai\Hotbars;

class HotbarManager {

	private array $hotbars;

	public function __construct() {
		$this->hotbars = [];
	}

	public function registerHotbar(Hotbar $hotbar) {
		$this->hotbars[$hotbar->getName()] = $hotbar;
	}

	public function getHotbar(string $name): ?Hotbar {
		return $this->hotbars[$name] ?? null;
	}

}