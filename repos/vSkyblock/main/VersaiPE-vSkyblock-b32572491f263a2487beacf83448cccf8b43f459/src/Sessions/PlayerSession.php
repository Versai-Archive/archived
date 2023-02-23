<?php

declare(strict_types=1);

namespace Skyblock\Sessions;

use Skyblock\Island\Island;
use pocketmine\player\Player;

class PlayerSession {

	private Player $player;
	private int $level = 0;
	private float $xp = 0.0;
	private int $coins = 0;
	private array $keys = [0, 0, 0, 0];
	private int $miningLevel;
	private int $miningXp;
	private int $farmingLevel;
	private int $farmingXp;
	private int $lumberjackLevel;
	private int $lumberjackXp;
	private ?Island $island;

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function getLevel(): int {
		return $this->level;
	}

	public function setLevel(int $level) {
		$this->level = $level;
	}

	public function addLevel(int $level) {
		$this->level += $level;
	}

	public function setXp(float $xp) {
		$this->xp = $xp;
	}

	public function getXp(): float {
		return $this->xp;
	}

	public function addXp(float|int $xp) {
		$this->xp = (float)$xp;
	}

	public function getCoins(): int {
		return $this->coins;
	}

	public function setCoins(int $coins) {
		$this->coins = $coins;
	}

	public function addCoins(int $coins) {
		$this->coins += $coins;
	}

	public function removeCoins(int $coins) {
		$this->coins -= $coins;
	}

	public function hasSufficentFunds(int $cost): bool {
		if ($this->coins >= $cost) return true;
		return false;
	}

	public function getKeys() {
		return $this->keys;
	}

	public function getCommonKeys(): int {
		return $this->keys[0];
	}

	public function removeCommonKeys(int $amount) {
		$this->keys[0] -= $amount;
	}

	public function addCommonKeys(int $amount) {
		$this->keys[0] += $amount;
	}

	public function setCommonKeys(int $amount) {
		$this->keys[0] = $amount;
	}

	public function getRareKeys(): int {
		return $this->keys[1];
	}

	public function removeRareKeys(int $amount) {
		$this->keys[1] -= $amount;
	}

	public function addRareKeys(int $amount) {
		$this->keys[1] += $amount;
	}

	public function setRareKeys(int $amount) {
		$this->keys[1] = $amount;
	}

	public function getMythicKeys(): int {
		return $this->keys[2];
	}

	public function removeMythicKeys(int $amount) {
		$this->keys[2] -= $amount;
	}

	public function addMythicKeys(int $amount) {
		$this->keys[2] += $amount;
	}

	public function setMythicKeys(int $amount) {
		$this->keys[2] = $amount;
	}

	public function getLegendaryKeys(): int {
		return $this->keys[3];
	}

	public function removeLegendayKeys(int $amount) {
		$this->keys[3] -= $amount;
	}

	public function addLegendaryKeys(int $amount) {
		$this->keys[3] += $amount;
	}

	public function setLegendaryKeys(int $amount) {
		$this->keys[3] = $amount;
	}

	public function getMiningLevel(): int {
		return $this->miningLevel;
	}

	public function setMiningLevel(int $level) {
		$this->miningLevel = $level;
	}

	public function addMiningLevel(int $level) {
		$this->miningLevel += $level;
	}

	public function getMiningXp(): int {
		return $this->miningXp;
	}

	public function addMiningXp(int $amount) {
		$this->miningXp += $amount;
	}

	public function setMiningXp(int $miningXp): void {
		$this->miningXp = $miningXp;
	}

	public function getFarmingLevel(): int {
		return $this->farmingLevel;
	}

	public function addFarmingLevel(int $levels) {
		$this->farmingLevel += $levels;
	}

	public function setFarmingLevel(int $level) {
		$this->farmingLevel = $level;
	}

	public function getFarmingXp(): int {
		return $this->farmingXp;
	}

	public function addFarmingXp(int $amount) {
		$this->farmingXp += $amount;
	}

	public function setFarmingXp(int $farmingXp): void {
		$this->farmingXp = $farmingXp;
	}

	public function getLumberjackLevel(): int {
		return $this->lumberjackLevel;
	}

	public function addLumberjackLevel(int $levels) {
		$this->lumberjackLevel += $levels;
	}

	public function setLumberjackLevel(int $level) {
		$this->lumberjackLevel = $level;
	}

	public function getLumberjackXp(): int {
		return $this->lumberjackXp;
	}

	public function addLumberjackXp(int $amount) {
		$this->lumberjackXp += $amount;
	}

	public function setLumberjackXp(int $lumberjackXp): void {
		$this->lumberjackXp = $lumberjackXp;
	}

	public function getIsland(): ?Island {
		return $this->island;
	}

	public function setIsland(Island $island): void {
		$this->island = $island;
	}
}