<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/21/2018
 * Time: 8:07 PM
 */

declare(strict_types=1);

namespace ARTulloss\Tap2;

use pocketmine\item\Item;

class Armor
{
	public const HELMET = [
		ITEM::LEATHER_HELMET,
		ITEM::CHAIN_HELMET,
		ITEM::GOLD_HELMET,
		ITEM::IRON_HELMET,
		ITEM::DIAMOND_HELMET
	];

	public const CHESTPLATE = [
		ITEM::LEATHER_CHESTPLATE,
		ITEM::CHAIN_CHESTPLATE,
		ITEM::GOLD_CHESTPLATE,
		ITEM::IRON_CHESTPLATE,
		ITEM::DIAMOND_CHESTPLATE
	];

	public const LEGGINGS = [
		ITEM::LEATHER_LEGGINGS,
		ITEM::CHAIN_LEGGINGS,
		ITEM::GOLD_LEGGINGS,
		ITEM::IRON_LEGGINGS,
		ITEM::DIAMOND_LEGGINGS
	];

	public const BOOTS = [
		ITEM::LEATHER_BOOTS,
		ITEM::CHAIN_BOOTS,
		ITEM::GOLD_BOOTS,
		ITEM::IRON_BOOTS,
		ITEM::DIAMOND_BOOTS
	];
}