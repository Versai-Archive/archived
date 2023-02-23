<?php

namespace ethaniccc\BotDuels\game;

use ethaniccc\BotDuels\bots\ComboBot;
use ethaniccc\BotDuels\bots\NoDebuffBot;
use ethaniccc\BotDuels\bots\SumoBot;
use ethaniccc\BotDuels\BotDuels;
use ethaniccc\BotDuels\map\MapData;
use ethaniccc\BotDuels\tasks\AsyncClosureTask;
use ethaniccc\BotDuels\tasks\GenerateLevelTask;
use ethaniccc\BotDuels\tasks\RemoveLevelTask;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
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
	public $status = self::STATUS_INIT;
	/** @var int - The difficulty of the bot */
	public $difficulty;
	/** @var int */
    public $type;
	/** @var Player - The player to fight against the bot */
	public $player;
	/** @var NoDebuffBot */
	public $bot;
	/** @var Level */
	public $level;
	/** @var MapData - Information about the map */
	public $mapData;
	/** @var bool - Boolean value for if the bot has spawned to the player. */
	public $botSpawned = false;
	public $winner = self::UNDETERMINED;

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
					$level = $this->player->getLevel();
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
					if ($chunk !== null) {
					    switch($this->type){
                            case BotDuels::TYPE_NODEBUFF:
                                $this->bot = new NoDebuffBot($this->level, $this->player->getSkin(), Entity::createBaseNBT($this->mapData->botSpawnPosition), $this->player->getName(), $this->difficulty);
                                break;
                            case BotDuels::TYPE_SUMO:
                                $this->bot = new SumoBot($this->level, $this->player->getSkin(), Entity::createBaseNBT($this->mapData->botSpawnPosition), $this->player->getName(), $this->difficulty);
                                break;
                            case BotDuels::TYPE_COMBO:
                                $this->bot = new ComboBot($this->level, $this->player->getSkin(), Entity::createBaseNBT($this->mapData->botSpawnPosition), $this->player->getName(), $this->difficulty);
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
					    switch($this->type){
                            case BotDuels::TYPE_NODEBUFF:
                                Server::getInstance()->broadcastMessage(TextFormat::WHITE . "{$this->player->getName()} won against the NoDebuff " . TextFormat::clean($this->bot->getNameTag()));
                                break;
                            case BotDuels::TYPE_SUMO:
                                Server::getInstance()->broadcastMessage(TextFormat::WHITE . "{$this->player->getName()} won against the Sumo " . TextFormat::clean($this->bot->getNameTag()));
                                break;
                            case BotDuels::TYPE_COMBO:
                                Server::getInstance()->broadcastMessage(TextFormat::WHITE . "{$this->player->getName()} won against the Combo " . TextFormat::clean($this->bot->getNameTag()));
                                break;
                        }
						break;
					case self::WINNER_BOT:
						if ($this->bot !== null) {
                            switch($this->type){
                                case BotDuels::TYPE_NODEBUFF:
                                    Server::getInstance()->broadcastMessage(TextFormat::GRAY . "{$this->player->getName()} lost against the NoDebuff " . TextFormat::clean($this->bot->getNameTag()));
                                    break;
                                case BotDuels::TYPE_SUMO:
                                    Server::getInstance()->broadcastMessage(TextFormat::GRAY . "{$this->player->getName()} lost against the Sumo " . TextFormat::clean($this->bot->getNameTag()));
                                    break;
                                case BotDuels::TYPE_COMBO:
                                    Server::getInstance()->broadcastMessage(TextFormat::GRAY . "{$this->player->getName()} lost against the Combo " . TextFormat::clean($this->bot->getNameTag()));
                                    break;
                            }
						}
						break;
				}
				foreach ($this->level->getPlayers() as $player) {
					$player->setGamemode(GameMode::SURVIVAL);
				}
				GameManager::getInstance()->remove($this);
				Server::getInstance()->unloadLevel($this->level, true);
				Server::getInstance()->getAsyncPool()->submitTask(new RemoveLevelTask("./worlds/{$this->level->getFolderName()}"));
				break;
		}
	}

	public function givePlayerItems(int $type){
	    switch($type){
            case BotDuels::TYPE_NODEBUFF:
                for ($i = 0; $i <= 35; ++$i) {
                    $this->player->getInventory()->setItem($i, Item::get(Item::SPLASH_POTION, 22, 1));
                }
                $sword = Item::get(Item::DIAMOND_SWORD);
                $sword->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10));
                $this->player->getInventory()->setItem(0, $sword);
                $enchantment = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10);
                $this->player->getInventory()->setItem(1, Item::get(Item::ENDER_PEARL, 0, 16));
                $helmet = Item::get(Item::DIAMOND_HELMET);
                $helmet->addEnchantment($enchantment);
                $chestplate = Item::get(Item::DIAMOND_CHESTPLATE);
                $chestplate->addEnchantment($enchantment);
                $leggings = Item::get(Item::DIAMOND_LEGGINGS);
                $leggings->addEnchantment($enchantment);
                $boots = Item::get(Item::DIAMOND_BOOTS);
                $boots->addEnchantment($enchantment);

                $this->player->getArmorInventory()->setHelmet($helmet);
                $this->player->getArmorInventory()->setChestplate($chestplate);
                $this->player->getArmorInventory()->setLeggings($leggings);
                $this->player->getArmorInventory()->setBoots($boots);
                $this->player->getInventory()->setHeldItemIndex(0);
                $this->player->removeAllEffects();
                $effect = new EffectInstance(Effect::getEffect(Effect::SPEED), 100000, 0);
                $this->player->addEffect($effect);
                break;
            case BotDuels::TYPE_SUMO:
                $this->player->getInventory()->setItem(0, Item::get(ItemIds::STEAK, 0, 64));
                $this->player->removeAllEffects();
                $speed = new EffectInstance(Effect::getEffect(Effect::SPEED), 100000, 0);
                $resistance = new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 100000, 3);
                $this->player->addEffect($speed);
                $this->player->addEffect($resistance);
                break;
            case BotDuels::TYPE_COMBO:
                $helmet = Item::get(ItemIds::IRON_HELMET, 0, 1);
                $helmet->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10));
                $chestplate = Item::get(ItemIds::IRON_CHESTPLATE, 0, 1);
                $chestplate->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10));
                $leggings = Item::get(ItemIds::IRON_LEGGINGS, 0, 1);
                $leggings->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10));
                $boots = Item::get(ItemIds::IRON_BOOTS, 0, 1);
                $boots->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 10));
                $this->player->getArmorInventory()->setHelmet($helmet);
                $this->player->getArmorInventory()->setChestplate($chestplate);
                $this->player->getArmorInventory()->setLeggings($leggings);
                $this->player->getArmorInventory()->setBoots($boots);

                $this->player->getInventory()->setItem(0, $helmet);
                $this->player->getInventory()->setItem(1, $chestplate);
                $this->player->getInventory()->setItem(2, $leggings);
                $this->player->getInventory()->setItem(3, $boots);

                $this->player->removeAllEffects();
                $speed = new EffectInstance(Effect::getEffect(Effect::SPEED), 100000, 0);
                $resistance = new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 100000, 1);
                $this->player->addEffect($speed);
                $this->player->addEffect($resistance);
                break;
        }
    }

}