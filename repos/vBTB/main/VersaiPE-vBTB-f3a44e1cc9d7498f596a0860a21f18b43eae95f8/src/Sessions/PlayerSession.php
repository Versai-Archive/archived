<?php

declare(strict_types=1);

namespace Versai\BTB\Sessions;

use pocketmine\player\Player;

class PlayerSession {

	private Player $player;

	const RANKED = 0;
	const UNRANKED = 1;

	private int $coins = 0;
	private int $rankedKills = 0;
	private int $rankedDeaths = 0;
	private int $unrankedKills = 0;
	private int $unrankedDeaths = 0;
	private int $rankedWins = 0;
	private int $rankedLosses = 0;
	private int $unrankedWins = 0;
	private int $unrankedLosses = 0;
	private int $bedsBroken = 0;
	private int $level = 0;
	private int $experience = 0;
	private int $elo = 250;

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function getCoins(): int {
		return $this->coins;
	}

	public function setCoins(int $coins): void{
		$this->coins = $coins;
	}

	public function addCoins(int $coins): void {
		$this->coins += $coins;
	}

	public function removeCoins(int $coins): void {
		$this->coins -= $coins;
	}

	public function hasSufficentCoins(int $required): bool {
		if ($this->coins >= $required) return true;
		return false;
	}

	public function getDeaths(): int {
		return $this->unrankedDeaths + $this->rankedDeaths;
	}

	public function getKills(): int {
		return $this->unrankedKills + $this->rankedKills;
	}

	public function addUnrankedKill() {
		$this->unrankedKills++;
	}

	public function addRankedKill() {
		$this->rankedKills++;
	}

	public function addUnrankedDeath() {
		$this->unrankedDeaths++;
	}

	public function addRankedDeath() {
		$this->rankedDeaths++;
	}

	public function getBedsBroken(): int {
		return $this->bedsBroken;
	}

	public function setBedsBroken(int $amount) {
		$this->bedsBroken = $amount;
	}

	public function addBedBroke() {
		$this->bedsBroken++;
	}

	public function getLevel() {
		return $this->level;
	}

	public function setLevel(int $level) {
		$this->level = $level;
	}

	public function addLevel() {
		$this->level++;
	}

	public function getExperience() {
		return $this->experience;
	}

	public function addExperience(int $amount) {
		$this->experience += $amount;
	}

	public function setExperience(int $experience) {
		$this->experience = $experience;
	}

	public function getElo() {
		return $this->elo;
	}

	public function addElo(int $amount) {
		$this->elo += $amount;
	}

	public function removeElo(int $amount) {
		$this->elo -= $amount;
	}

	public function setElo(int $elo) {
		$this->elo = $elo;
	}

	public function getRankedWins(): int {
		return $this->rankedWins;
	}

	public function setRankedWins(int $wins) {
		$this->rankedWins = $wins;
	}

	public function addRankedWin() {
		$this->rankedWins++;
	}

	public function getRankedLosses(): int {
		return $this->rankedLosses;
	}

	public function setRankedLosses(int $amount) {
		$this->rankedLosses = $amount;
	}

	public function addRankedLoss() {
		$this->rankedLosses++;
	}

	public function getUnrankedWins(): int {
		return $this->unrankedWins;
	}

	public function addUnrankedWin() {
		$this->unrankedWins++;
	}

	public function getUnrankedLosses(): int {
		return $this->unrankedLosses;
	}

	public function setUnrankedLosses(int $amount) {
		$this->unrankedLosses = $amount;
	}

	public function addUnrankedLoss() {
		$this->unrankedLosses++;
	}
}