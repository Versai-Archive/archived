<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Sessions;

use pocketmine\player\Player;
use Versai\OneBlock\OneBlock\OneBlock;

class PlayerSession {

	private int $kills = 0;
	private int $deaths = 0;
	private int $blocksPlaced = 0;
	private int $blocksBroken = 0;
	private float|int $coins = 0;

	private ?OneBlock $island = null;

	private Player $player;

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function getCoins(): float|int {
		return $this->coins;
	}

	public function setCoins(float|int $amount): void {
		$this->coins = $amount;
	}

	public function addCoins(float|int $amount): void {
		$this->coins += $amount;
	}

	public function removeCoins(float|int $amount): void {
		$this->coins -= $amount;
	}

	public function hasSufficientFunds(float|int $cost): bool {
		if ($this->coins >= $cost) {
			return true;
		}
		return false;
	}

	public function getKills(): int {
		return $this->kills;
	}

	public function setKills(int $amount): void {
		$this->kills = $amount;
	}

	public function addKill(): void {
		$this->kills++;
	}

	public function getDeaths(): int {
		return $this->deaths;
	}

	public function setDeaths(int $amount): void {
		$this->deaths = $amount;
	}

	public function addDeath(): void {
		$this->deaths++;
	}

	public function getBlocksBroken(): int {
		return $this->blocksBroken;
	}

	public function setBlocksBroken(int $amount): void {
		$this->blocksBroken = $amount;
	}

	public function addBlocksBroken(int $amount): void {
		$this->blocksBroken += $amount;
	}

	public function getBlocksPlaced(): int {
		return $this->blocksPlaced;
	}

	public function setBlocksPlaced(int $amount): void {
		$this->blocksPlaced = $amount;
	}

	public function addBlocksPlaced(int $amount): void {
		$this->blocksPlaced += $amount;
	}

	public function setIsland(?OneBlock $island): void {
		$this->island = $island;
	}

	public function getIsland(): ?OneBlock {
		return $this->island;
	}

	public function hasIsland(): bool {
		return !is_null($this->island);
	}

	public function isOnIsland(): bool {
		return str_starts_with($this->player->getWorld()->getFolderName(), "ob-");
	}
}