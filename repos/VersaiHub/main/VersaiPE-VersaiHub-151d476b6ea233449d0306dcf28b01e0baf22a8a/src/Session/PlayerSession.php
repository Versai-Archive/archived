<?php

declare(strict_types=1);

namespace Versai\Session;

use pocketmine\player\Player;

class PlayerSession {

	private bool $pvpEnabled = false;

	private int $kills = 0;

	private Player $player;

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function isPvpEnabled(): bool {
		return $this->pvpEnabled;
	}

	public function togglePvP(): void {
		$this->pvpEnabled = !$this->pvpEnabled;
	}

    public function getKills(): int {
        return $this->kills;
    }

    public function setKills(int $amount): void {
        $this->kills = $amount;
    }

}