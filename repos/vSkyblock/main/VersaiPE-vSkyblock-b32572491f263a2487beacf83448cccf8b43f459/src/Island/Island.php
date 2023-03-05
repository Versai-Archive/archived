<?php

declare(strict_types=1);

namespace Skyblock\Island;

use FilesystemIterator;
use Skyblock\Main;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Island {

	const ranks = ["owner", "helper", "co-owner"];
	const rank_permissions = ["island.interact", "island.place", "island.break", "island.kick", "island.ban", "island.lock", "island.unlock", "island.invite"];

	private $owner;
	private array $members;
	private string $name;
	private int $level;
	private int $xp;
	private int $spawners;
	private array $banned;
	private bool $locked;
	private array $stats;
	private array $settings;

	private $defaultPermissions = [
		"member" => ["island.place", "island.break"],
		"co-owner" => ["island.place", "island.break", "island.kick", "island.invite"],
		"owner" => ["island.place", "island.break", "island.kick", "island.ban", "island.lock", "island.unlock", "island.invite"]
	];

	private $islandChat;
	private $invited;

	public function __construct(Player $owner) {
		$db = Main::getInstance()->getDatabase();
		$data = $db->getIslandData($owner)[0] ?? null;

		if ($db->playerHasIsland($owner)) {
			$this->owner = $owner;
			$this->name = "test";
			$this->members = json_decode($data["members"]);
			$this->level = (int)$data["level"];
			$this->xp = (int)$data["xp"];
			$this->spawners = (int)$data["spawners"];
			$this->banned = json_decode($data["banned"]);
			$this->locked = (bool)$data["locked"];
			$this->stats = (array)json_decode($data["stats"]);
			$this->settings = (array)json_decode($data["settings"]);
			$this->islandChat = [];
			$this->invited = json_decode($data["invited"]);
			return;
		}

		$this->name = "testing";
		$this->owner = $owner;
		$this->members = [];
		$this->level = 0;
		$this->xp = 0;
		$this->spawners = 0;
		$this->banned = [];
		$this->locked = false;
		$this->stats = [];
		$this->settings = [];
		$this->invited = [];
		$this->islandChat = [];
	}

	public function getStats(): array {
		return $this->stats;
	}

	public function getOwner(): Player {
		return $this->owner;
	}

	public function getName(): string {
		return $this->name;
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
		$this->xp = (int)$xp;
	}

	public function getXp(): float {
		return $this->xp;
	}

	public function addXp(float|int $xp) {
		$this->xp = (int)$xp;
	}

	public function getSpawners(): int {
		return $this->spawners;
	}

	public function addSpawners(int $spawners) {
		$this->spawners += $spawners;
	}

	public function removeSpawners(int $spawners) {
		$this->spawners -= $spawners;
	}

	public function getBanned(): array {
		return $this->banned;
	}

	public function addBanned(Player $player) {
		$this->banned[] = $player->getName();
	}

	public function removeBanned(Player $player) {
		$this->banned = array_diff($this->banned, [$player->getName()]);
	}

	public function getLocked(): bool {
		return $this->locked;
	}

	public function setLocked(bool $locked) {
		$this->locked = $locked;
	}

	public function getSettings(): array {
		return $this->settings;
	}

	public function setSettings(array $settings) {
		$this->settings = $settings;
	}

	public function getInvited(): array {
		return $this->invited;
	}

	public function sendInvite(Player $player) {
		$this->invited[] = $player->getName();
	}

	public function removeInvite(Player $player) {
		$this->invited = array_diff($this->invited, [$player->getName()]);
	}

	public function getIslandChat(): array {
		return $this->islandChat;
	}

	public function addIslandChat(Player $player) {
		$this->islandChat[] = $player->getName();
	}

	public function removeIslandChat(Player $player) {
		$this->islandChat = array_diff($this->islandChat, [$player->getName()]);
	}

	public function getMembers(): array {
		return $this->members;
	}

	public function addMember(Player $player) {
		$this->members[] = $player->getName();
	}

	public function removeMember(Player $player) {
		$this->members = array_diff($this->members, [$player->getName()]);
	}

	public static function generateIsland(Player $player): void {
		$worldName = Main::getInstance()->getConfig()->getNested("island.base");
		$duplicateName = $player->getXuid();
		if(Server::getInstance()->getWorldManager()->isWorldLoaded($worldName)) {
			self::getWorldByNameNonNull($worldName)->save();
		}

		mkdir(Server::getInstance()->getDataPath() . "/worlds/$duplicateName");

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Server::getInstance()->getDataPath() . "/worlds/$worldName", FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
		/** @var SplFileInfo $fileInfo */
		foreach($files as $fileInfo) {
			if($filePath = $fileInfo->getRealPath()) {
				if($fileInfo->isFile()) {
					@copy($filePath, str_replace($worldName, $duplicateName, $filePath));
				} else {
					mkdir(str_replace($worldName, $duplicateName, $filePath));
				}
			}
		}
	}

	private static function getWorldByNameNonNull(string $name): World {
		$world = Server::getInstance()->getWorldManager()->getWorldByName($name);
		if($world === null) {
			throw new AssumptionFailedError("Required world $name is null");
		}

		return $world;
	}

	public function getWorld(): ?World {
		if (!Server::getInstance()->getWorldManager()->isWorldLoaded($this->owner->getXuid())) {
			Server::getInstance()->getWorldManager()->loadWorld($this->owner->getXuid());
		}
		return Main::getInstance()->getServer()->getWorldManager()->getWorldByName($this->owner->getXuid()) ?? null;
	}
}