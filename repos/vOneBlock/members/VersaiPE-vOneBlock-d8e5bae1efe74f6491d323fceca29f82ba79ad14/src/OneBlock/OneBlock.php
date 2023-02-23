<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\OneBlock;

use pocketmine\player\Player;
use pocketmine\world\World;
use Versai\OneBlock\Events\IslandLevelUpEvent;
use Versai\OneBlock\Main;

class OneBlock {

	private string $description = "";
	private string $type = OneBlockType::DEFAULT;
	private array $members = [];
	private int $level = 0;
	private int $blocksBroken = 0;
	private int $prestige = 0;
	private World $world;
	// The owners xuid
	private string $owner;
	private int $blocksBrokenTotal = 0;

	public function __construct(string $owner, World $world) {
		$this->owner = $owner;
		$this->world = $world;

		if (Main::getInstance()->getDatabase()->playerHasIsland($owner)) {
			$data = Main::getInstance()->getDatabase()->getIslandByXuid($owner)[0];
			$this->description = $data["description"];
			$this->type = $data["type"];
			var_dump(json_decode($data["members"], true));
			$this->members = json_decode($data["members"], true);
			$this->level = (int)$data["level"];
			$this->blocksBroken = (int)$data["blocks_broken_count"];
			$this->blocksBrokenTotal = (int)$data["blocks_broken_total"];
		}

	}

	/**
	 * Returns the XUID of the islands owner
	 */
	public function getOwner(): string {
		return $this->owner;
	}

	public function getWorld(): World {
		return $this->world;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function setDescription(string $description) {
		$this->description = $description;
	}

	public function getType(): string {
		return $this->type;
	}

	public function setType(string $type) {
		$this->type = $type;
	}

	public function setMembers(array $members) {
		$this->members = $members;
	}

	public function setMemberPermissions(string $member, array $perms) {
		$this->members[$member] = $perms;
	}

	public function addMember(string $xuid, array $permissions) {
		$this->members[$xuid] = $permissions;
	}

	public function getMembers(): array {
		return $this->members;
	}

	public function getRankFromPerms(array $perms): string {
		$data = yaml_parse_file(Main::getInstance()->getDataFolder() . "permissions.yml");

		if ($perms === $data["member"]) {
			return "Member";
		}
		if ($perms === $data["admin"]) {
			return "Admin";
		}
		if ($perms === $data["co-owner"]) {
			return "Co-Owner";
		}
		return "Custom";
	}

	public function memberHasPermission(string $player, string $permission): bool {
		if (isset($this->members[$player])) {
			return in_array($permission, $this->members[$player]);
		}
		return false;
	}

	public function isPlayerBanned(string $xuid): bool {
		if (!isset($this->members[$xuid])) {
			return false;
		}
		if (in_array(OneBlockPermissions::BANNED, $this->members[$xuid])) {
			return true;
		}
		return false;
	}

	public function getMemberPermissions(string $xuid): ?array {
		if (isset($this->members[$xuid])) {
			return $this->members[$xuid];
		}
		return null;
	}

	public function getMembersForDatabase() {
		return json_encode($this->members);
	}

	public function getLevel(): int {
		return $this->level;
	}

	public function setLevel(int $level) {
		$this->level = $level;
	}

	public function addLevel() {
		$this->level++;
		$this->blocksBroken = 0;
	}

	public function setBlocksBroken(int $amount): void {
		$this->blocksBroken = $amount;
	}

	public function addBlockBreak() {
		$this->blocksBroken++;
		$this->blocksBrokenTotal++;
		if ($this->canLevelUp()) {
			$this->levelUp();
		}
	}

	public function getBlocksBroken(): int {
		return $this->blocksBroken;
	}

	public function getBlocksBrokenTotal(): int {
		return $this->blocksBrokenTotal;
	}

	public function setTotalBlocksBroken(int $total) {
		$this->blocksBrokenTotal = $total;
	}

	/**
	 * @description Will calculate the amount of blocks that needs to be broken to get to the next level
	 */
	public function calculateBlocksNeededForNextLevel() {
		$g = 150 * $this->level;
		return 350 + (($g + $this->level)-$this->level);
	}

	/**
	 * @description Returns how many more blocks need to be broken to reach next level
	 */
	public function calculateBlocksToReachNextLevel(): int {
		$needed = $this->calculateBlocksNeededForNextLevel();
		return $needed - $this->blocksBroken;
	}

	public function levelUp() {
		if ($this->canLevelUp()) {
			$this->addLevel();
			(new IslandLevelUpEvent($this))->call();
		}
		return;
	}

	public function canLevelUp(): bool {
		if (count(Main::getInstance()->getConfig()->getNested("levels")) < $this->getLevel()) return false;
		if ($this->calculateBlocksToReachNextLevel() <= 0) return true;
		return false;
	}

	public function isMaxLevel(): bool {
		if (count(Main::getInstance()->getConfig()->getNested("levels")) - 1 < $this->getLevel()) return true;
		return false;
	}
}