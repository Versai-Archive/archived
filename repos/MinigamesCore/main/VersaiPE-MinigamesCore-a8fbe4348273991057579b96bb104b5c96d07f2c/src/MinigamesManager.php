<?php

declare(strict_types=1);

namespace Versai;

class MinigamesManager {

	/** @var Minigame[] */
	private array $minigames;

	public function __construct() {
		$this->minigames = [];
	}

	public function registerMinigame(Minigame $minigame): void {
		$this->minigames[] += $minigame;
	}

	/** @return Minigame[] */
	public function getMinigames(): array {
		return $this->minigames;
	}

	/** @return String[] */
	public function getMinigamesName(): array {
		$names = [];
		foreach($this->minigames as $minigame) {
			$names[] .= $minigame->getName();
		}
		return $names;
	}

	// TODO: Correct this and make it functional
	public function getMinigameByName(string $name): ?Minigame {
		foreach($this->minigames as $minigame) {
			if ($minigame->getName() === $name) {
				return $minigame;
			}
		}
		return null;
	}

}