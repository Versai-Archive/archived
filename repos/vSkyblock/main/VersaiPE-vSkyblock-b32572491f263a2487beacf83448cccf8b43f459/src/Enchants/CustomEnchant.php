<?php

declare(strict_types=1);

namespace Skyblock\Enchants;

use Skyblock\Main;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\lang\Translatable;
use pocketmine\Server;

class CustomEnchant extends Enchantment {

	/**
	 * Snippet 14-46 PiggyCustomEnchants
	 */
	public string $name;
	public int $rarity;
	public int $maxLevel;
	public string $displayName;
	public array $extraData;
	public int $cooldownDuration;
	public int $chance;
	public int $id;

	const TYPE_HAND = 0;
	const TYPE_ANY_INVENTORY = 1;
	const TYPE_INVENTORY = 2;
	const TYPE_ARMOR_INVENTORY = 3;
	const TYPE_HELMET = 4;
	const TYPE_CHESTPLATE = 5;
	const TYPE_LEGGINGS = 6;
	const TYPE_BOOTS = 7;

	const ITEM_TYPE_GLOBAL = 0;
	const ITEM_TYPE_DAMAGEABLE = 1;
	const ITEM_TYPE_WEAPON = 2;
	const ITEM_TYPE_SWORD = 3;
	const ITEM_TYPE_BOW = 4;
	const ITEM_TYPE_TOOLS = 5;
	const ITEM_TYPE_PICKAXE = 6;
	const ITEM_TYPE_AXE = 7;
	const ITEM_TYPE_SHOVEL = 8;
	const ITEM_TYPE_HOE = 9;
	const ITEM_TYPE_ARMOR = 10;
	const ITEM_TYPE_HELMET = 11;
	const ITEM_TYPE_CHESTPLATE = 12;
	const ITEM_TYPE_LEGGINGS = 13;
	const ITEM_TYPE_BOOTS = 14;
	const ITEM_TYPE_COMPASS = 15;

	public function __construct(int $id)
	{
		$this->id = $id;
		if(!isset($data)) {
			Main::getInstance()->getLogger()->critical("Enchantment with id {$this->id} not found");
			return;
		}
		$this->name = (string)$this->getEnchantmentData("name");
		$this->rarity = (int)$this->getEnchantmentData("rarity");
		$this->maxLevel = (int)$this->getEnchantmentData("max_level");
		$this->displayName = (string)$this->getEnchantmentData("display_name");
		$this->extraData = (array)$this->getEnchantmentData("extra_data");
		$this->cooldownDuration = (int)$this->getEnchantmentData("cooldown_duration");
		$this->chance = (int)$this->getEnchantmentData("chance");
		$this->itemType = (int)$this->getEnchantmentData("item_type");

		parent::__construct($this->name, $this->rarity, ItemFlags::ALL, ItemFlags::ALL, $this->maxLevel);
	}

	public function getEnchantmentData(string $info) {
		return Main::getInstance()->getConfig()->getNested("enchants.{$this->id}.{$info}");
	}


}