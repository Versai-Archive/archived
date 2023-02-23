<?php

namespace ethaniccc\BotDuels\game;

use ethaniccc\BotDuels\bots\NoDebuffBot;
use ethaniccc\BotDuels\bots\SumoBot;
use ethaniccc\BotDuels\BotDuels;
use ethaniccc\BotDuels\map\MapData;
use ethaniccc\BotDuels\tasks\GenerateLevelTask;
use ethaniccc\BotDuels\tasks\RemoveLevelTask;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\world\World;
use pocketmine\world\Position;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class DuelGame {

	public const STATUS_INIT = 0;
	public const FIRST_RUN = 1;
	public const STATUS_RUNNING = 2;
	public const STATUS_END = 3;

	public const UNDETERMINED = -1;
	public const WINNER_PLAYER = 0;
	public const WINNER_BOT = 1;

	/** @var int - The current status of the Duel game */
	public int $status = self::STATUS_INIT;
	/** @var int - The difficulty of the bot */
	public int $difficulty;
	/** @var int */
    public int $type;
	/** @var Player - The player to fight against the bot */
	public Player $player;
	public $bot;
	/** @var World */
	public World $level;
	/** @var Location */
    public Location $location;
	/** @var MapData - Information about the map */
	public MapData $mapData;
	/** @var bool - Boolean value for if the bot has spawned to the player. */
	public bool $botSpawned = false;

	public int $winner = self::UNDETERMINED;

	public function __construct(Player $player, int $type, int $difficulty, MapData $mapData) {
		$this->player = $player;
		$this->type = $type;
		$this->difficulty = $difficulty;
		$this->mapData = $mapData;
		$newLevelname = substr(sha1(rand()), 0, 15);
		Server::getInstance()->getAsyncPool()->submitTask(new GenerateLevelTask($mapData, $this->player->getName() . "-" . $newLevelname, $this));
	}

	public function tick(): void {
		switch ($this->status) {
			case self::FIRST_RUN:
				if ($this->player->isClosed() || !$this->player->isAlive()) {
					$this->status = self::STATUS_END;
					$this->winner = self::WINNER_BOT;
					$this->tick();
					return;
				}
				$this->player->teleport(Position::fromObject($this->mapData->playerSpawnPosition, $this->level));
				$this->givePlayerItems($this->type);
				$this->status = self::STATUS_RUNNING;
                $this->player->sendMessage(TextFormat::AQUA . "You are playing on {$this->mapData->name} by " . implode(",", $this->mapData->authors));
				break;
			case self::STATUS_RUNNING:
				if ($this->player->isClosed() || !$this->player->isAlive()) {
					$this->status = self::STATUS_END;
					$this->winner = self::WINNER_BOT;
					$this->tick();
					return;
				} else {
					$level = $this->player->getWorld();
					if ($level === null) {
						$this->status = self::STATUS_END;
						$this->winner = self::WINNER_BOT;
						$this->tick();
					} else {
						if ($level->getFolderName() !== $this->level->getFolderName()) {
							$this->status = self::STATUS_END;
							$this->winner = self::WINNER_BOT;
							$this->tick();
						}
					}
				}
				if (!$this->botSpawned) {
					$chunkX = floor($this->mapData->botSpawnPosition->x) >> 4;
					$chunkZ = floor($this->mapData->botSpawnPosition->z) >> 4;
					$chunk = $this->level->getChunk($chunkX, $chunkZ);
					$this->location = new Location($this->mapData->botSpawnPosition->x, $this->mapData->botSpawnPosition->y, $this->mapData->botSpawnPosition->z, $this->level, 0, 0);
					if ($chunk !== null) {
					    switch($this->type){
                            case BotDuels::TYPE_NODEBUFF:
                                $this->bot = new NoDebuffBot($this->location, $this->player->getSkin(), $this->player->getName(), $this->difficulty);
                                break;
                            case BotDuels::TYPE_SUMO:
                                $this->bot = new SumoBot($this->location, $this->player->getSkin(), $this->player->getName(), $this->difficulty);
                                break;
                        }
						$this->bot->spawnToAll();
						$this->bot->setCanSaveWithChunk(false);
						$this->botSpawned = true;
					}
				} else {
					if (!$this->bot->isAlive()) {
						$this->status = self::STATUS_END;
						$this->winner = self::WINNER_PLAYER;
					}
				}
				break;
			case self::STATUS_END:
				switch ($this->winner) {
					case self::WINNER_PLAYER:
					    Server::getInstance()->broadcastMessage(TextFormat::WHITE . "{$this->player->getName()} won against the {$this->bot->getType()} " . TextFormat::clean($this->bot->getNameTag()));
						break;
					case self::WINNER_BOT:
						if ($this->bot !== null) {
                            Server::getInstance()->broadcastMessage(TextFormat::GRAY . "{$this->player->getName()} lost against the {$this->bot->getType()} " . TextFormat::clean($this->bot->getNameTag()));
						}
						break;
				}
				foreach ($this->level->getPlayers() as $player) {
					$player->setGamemode(GameMode::SURVIVAL());
				}
				GameManager::getInstance()->remove($this);
				Server::getInstance()->getWorldManager()->unloadWorld($this->level, true);
				Server::getInstance()->getAsyncPool()->submitTask(new RemoveLevelTask("./worlds/{$this->level->getFolderName()}"));
				break;
		}
	}

	public function givePlayerItems(int $type){
	    switch($type){
            case BotDuels::TYPE_NODEBUFF:
                for ($i = 0; $i <= 35; ++$i) {
                    $this->player->getInventory()->setItem($i, VanillaItems::STRONG_HEALING_SPLASH_POTION());
                }
                $enchantment = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10);
                $sword = VanillaItems::DIAMOND_SWORD();
                $sword->addEnchantment($enchantment);
                $this->player->getInventory()->setItem(0, $sword);
                $this->player->getInventory()->setItem(1, VanillaItems::ENDER_PEARL()->setCount(16));
                $helmet = VanillaItems::DIAMOND_HELMET();
                $helmet->addEnchantment($enchantment);
                $chestplate = VanillaItems::DIAMOND_CHESTPLATE();
                $chestplate->addEnchantment($enchantment);
                $leggings = VanillaItems::DIAMOND_LEGGINGS();
                $leggings->addEnchantment($enchantment);
                $boots = VanillaItems::DIAMOND_BOOTS();
                $boots->addEnchantment($enchantment);

                $this->player->getArmorInventory()->setHelmet($helmet);
                $this->player->getArmorInventory()->setChestplate($chestplate);
                $this->player->getArmorInventory()->setLeggings($leggings);
                $this->player->getArmorInventory()->setBoots($boots);
                $this->player->getInventory()->setHeldItemIndex(0);
                $this->player->getEffects()->clear();
                $effect = new EffectInstance(VanillaEffects::SPEED(), 100000, 0, false);
                $this->player->getEffects()->add($effect);
                break;
            case BotDuels::TYPE_SUMO:
                $this->player->getInventory()->setItem(0, VanillaItems::STEAK()->setCount(64));
                $this->player->getEffects()->clear();
                $speed = new EffectInstance(VanillaEffects::SPEED(), 100000, 0, false);
                $resistance = new EffectInstance(VanillaEffects::RESISTANCE(), 100000, 3, false);
                $this->player->getEffects()->add($speed);
                $this->player->getEffects()->add($resistance);
                break;
        }
    }

}