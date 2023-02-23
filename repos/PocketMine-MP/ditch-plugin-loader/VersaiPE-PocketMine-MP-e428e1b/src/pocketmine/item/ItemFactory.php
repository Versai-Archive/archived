<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\SkullType;
use pocketmine\block\utils\TreeType;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Living;
use pocketmine\nbt\tag\CompoundTag;
use function constant;
use function defined;
use function explode;
use function is_a;
use function is_numeric;
use function str_replace;
use function strtoupper;
use function trim;

/**
 * Manages Item instance creation and registration
 */
class ItemFactory{

	/** @var Item[] */
	private static $list = [];

	/** @var Item|null */
	private static $air = null;

	public static function init() : void{
		self::$list = []; //in case of re-initializing

		self::register(new Apple(Item::APPLE, 0, "Apple"));
		self::register(new Arrow(Item::ARROW, 0, "Arrow"));
		self::register(new Axe(Item::DIAMOND_AXE, "Diamond Axe", TieredTool::TIER_DIAMOND));
		self::register(new Axe(Item::GOLDEN_AXE, "Gold Axe", TieredTool::TIER_GOLD));
		self::register(new Axe(Item::IRON_AXE, "Iron Axe", TieredTool::TIER_IRON));
		self::register(new Axe(Item::STONE_AXE, "Stone Axe", TieredTool::TIER_STONE));
		self::register(new Axe(Item::WOODEN_AXE, "Wooden Axe", TieredTool::TIER_WOODEN));
		self::register(new BakedPotato(Item::BAKED_POTATO, 0, "Baked Potato"));
		self::register(new Beetroot(Item::BEETROOT, 0, "Beetroot"));
		self::register(new BeetrootSeeds(Item::BEETROOT_SEEDS, 0, "Beetroot Seeds"));
		self::register(new BeetrootSoup(Item::BEETROOT_SOUP, 0, "Beetroot Soup"));
		self::register(new BlazeRod(Item::BLAZE_ROD, 0, "Blaze Rod"));
		self::register(new Book(Item::BOOK, 0, "Book"));
		self::register(new Boots(Item::CHAIN_BOOTS, 0, "Chainmail Boots", new ArmorTypeInfo(1, 196)));
		self::register(new Boots(Item::DIAMOND_BOOTS, 0, "Diamond Boots", new ArmorTypeInfo(3, 430)));
		self::register(new Boots(Item::GOLDEN_BOOTS, 0, "Gold Boots", new ArmorTypeInfo(1, 92)));
		self::register(new Boots(Item::IRON_BOOTS, 0, "Iron Boots", new ArmorTypeInfo(2, 196)));
		self::register(new Boots(Item::LEATHER_BOOTS, 0, "Leather Boots", new ArmorTypeInfo(1, 66)));
		self::register(new Bow(Item::BOW, 0, "Bow"));
		self::register(new Bowl(Item::BOWL, 0, "Bowl"));
		self::register(new Bread(Item::BREAD, 0, "Bread"));
		self::register(new Bucket(Item::BUCKET, 0, "Bucket"));
		self::register(new Carrot(Item::CARROT, 0, "Carrot"));
		self::register(new Chestplate(Item::CHAIN_CHESTPLATE, 0, "Chainmail Chestplate", new ArmorTypeInfo(5, 241)));
		self::register(new Chestplate(Item::DIAMOND_CHESTPLATE, 0, "Diamond Chestplate", new ArmorTypeInfo(8, 529)));
		self::register(new Chestplate(Item::GOLDEN_CHESTPLATE, 0, "Gold Chestplate", new ArmorTypeInfo(5, 113)));
		self::register(new Chestplate(Item::IRON_CHESTPLATE, 0, "Iron Chestplate", new ArmorTypeInfo(6, 241)));
		self::register(new Chestplate(Item::LEATHER_CHESTPLATE, 0, "Leather Tunic", new ArmorTypeInfo(3, 81)));
		self::register(new ChorusFruit(Item::CHORUS_FRUIT, 0, "Chorus Fruit"));
		self::register(new Clock(Item::CLOCK, 0, "Clock"));
		self::register(new Clownfish(Item::CLOWNFISH, 0, "Clownfish"));
		self::register(new Coal(Item::COAL, 0, "Coal"));
		self::register(new Coal(Item::COAL, 1, "Charcoal"));
		self::register(new CocoaBeans(Item::DYE, 3, "Cocoa Beans"));
		self::register(new Compass(Item::COMPASS, 0, "Compass"));
		self::register(new CookedChicken(Item::COOKED_CHICKEN, 0, "Cooked Chicken"));
		self::register(new CookedFish(Item::COOKED_FISH, 0, "Cooked Fish"));
		self::register(new CookedMutton(Item::COOKED_MUTTON, 0, "Cooked Mutton"));
		self::register(new CookedPorkchop(Item::COOKED_PORKCHOP, 0, "Cooked Porkchop"));
		self::register(new CookedRabbit(Item::COOKED_RABBIT, 0, "Cooked Rabbit"));
		self::register(new CookedSalmon(Item::COOKED_SALMON, 0, "Cooked Salmon"));
		self::register(new Cookie(Item::COOKIE, 0, "Cookie"));
		self::register(new DriedKelp(Item::DRIED_KELP, 0, "Dried Kelp"));
		self::register(new Egg(Item::EGG, 0, "Egg"));
		self::register(new EnderPearl(Item::ENDER_PEARL, 0, "Ender Pearl"));
		self::register(new ExperienceBottle(Item::EXPERIENCE_BOTTLE, 0, "Bottle o' Enchanting"));
		self::register(new Fertilizer(Item::DYE, 15, "Bone Meal"));
		self::register(new FishingRod(Item::FISHING_ROD, 0, "Fishing Rod"));
		self::register(new FlintSteel(Item::FLINT_STEEL, 0, "Flint and Steel"));
		self::register(new GlassBottle(Item::GLASS_BOTTLE, 0, "Glass Bottle"));
		self::register(new GoldenApple(Item::GOLDEN_APPLE, 0, "Golden Apple"));
		self::register(new GoldenAppleEnchanted(Item::ENCHANTED_GOLDEN_APPLE, 0, "Enchanted Golden Apple"));
		self::register(new GoldenCarrot(Item::GOLDEN_CARROT, 0, "Golden Carrot"));
		self::register(new Helmet(Item::CHAIN_HELMET, 0, "Chainmail Helmet", new ArmorTypeInfo(2, 166)));
		self::register(new Helmet(Item::DIAMOND_HELMET, 0, "Diamond Helmet", new ArmorTypeInfo(3, 364)));
		self::register(new Helmet(Item::GOLDEN_HELMET, 0, "Gold Helmet", new ArmorTypeInfo(2, 78)));
		self::register(new Helmet(Item::IRON_HELMET, 0, "Iron Helmet", new ArmorTypeInfo(2, 166)));
		self::register(new Helmet(Item::LEATHER_HELMET, 0, "Leather Cap", new ArmorTypeInfo(1, 56)));
		self::register(new Hoe(Item::DIAMOND_HOE, "Diamond Hoe", TieredTool::TIER_DIAMOND));
		self::register(new Hoe(Item::GOLDEN_HOE, "Golden Hoe", TieredTool::TIER_GOLD));
		self::register(new Hoe(Item::IRON_HOE, "Iron Hoe", TieredTool::TIER_IRON));
		self::register(new Hoe(Item::STONE_HOE, "Stone Hoe", TieredTool::TIER_STONE));
		self::register(new Hoe(Item::WOODEN_HOE, "Wooden Hoe", TieredTool::TIER_WOODEN));
		self::register(new Item(Item::BLAZE_POWDER, 0, "Blaze Powder"));
		self::register(new Item(Item::BLEACH, 0, "Bleach")); //EDU
		self::register(new Item(Item::BONE, 0, "Bone"));
		self::register(new Item(Item::BRICK, 0, "Brick"));
		self::register(new Item(Item::CHORUS_FRUIT_POPPED, 0, "Popped Chorus Fruit"));
		self::register(new Item(Item::CLAY_BALL, 0, "Clay"));
		self::register(new Item(Item::COMPOUND, 0, "Salt"));
		self::register(new Item(Item::COMPOUND, 1, "Sodium Oxide"));
		self::register(new Item(Item::COMPOUND, 2, "Sodium Hydroxide"));
		self::register(new Item(Item::COMPOUND, 3, "Magnesium Nitrate"));
		self::register(new Item(Item::COMPOUND, 4, "Iron Sulphide"));
		self::register(new Item(Item::COMPOUND, 5, "Lithium Hydride"));
		self::register(new Item(Item::COMPOUND, 6, "Sodium Hydride"));
		self::register(new Item(Item::COMPOUND, 7, "Calcium Bromide"));
		self::register(new Item(Item::COMPOUND, 8, "Magnesium Oxide"));
		self::register(new Item(Item::COMPOUND, 9, "Sodium Acetate"));
		self::register(new Item(Item::COMPOUND, 10, "Luminol"));
		self::register(new Item(Item::COMPOUND, 11, "Charcoal")); //??? maybe bug
		self::register(new Item(Item::COMPOUND, 12, "Sugar")); //??? maybe bug
		self::register(new Item(Item::COMPOUND, 13, "Aluminium Oxide"));
		self::register(new Item(Item::COMPOUND, 14, "Boron Trioxide"));
		self::register(new Item(Item::COMPOUND, 15, "Soap"));
		self::register(new Item(Item::COMPOUND, 16, "Polyethylene"));
		self::register(new Item(Item::COMPOUND, 17, "Rubbish"));
		self::register(new Item(Item::COMPOUND, 18, "Magnesium Salts"));
		self::register(new Item(Item::COMPOUND, 19, "Sulphate"));
		self::register(new Item(Item::COMPOUND, 20, "Barium Sulphate"));
		self::register(new Item(Item::COMPOUND, 21, "Potassium Chloride"));
		self::register(new Item(Item::COMPOUND, 22, "Mercuric Chloride"));
		self::register(new Item(Item::COMPOUND, 23, "Cerium Chloride"));
		self::register(new Item(Item::COMPOUND, 24, "Tungsten Chloride"));
		self::register(new Item(Item::COMPOUND, 25, "Calcium Chloride"));
		self::register(new Item(Item::COMPOUND, 26, "Water")); //???
		self::register(new Item(Item::COMPOUND, 27, "Glue"));
		self::register(new Item(Item::COMPOUND, 28, "Hypochlorite"));
		self::register(new Item(Item::COMPOUND, 29, "Crude Oil"));
		self::register(new Item(Item::COMPOUND, 30, "Latex"));
		self::register(new Item(Item::COMPOUND, 31, "Potassium Iodide"));
		self::register(new Item(Item::COMPOUND, 32, "Sodium Fluoride"));
		self::register(new Item(Item::COMPOUND, 33, "Benzene"));
		self::register(new Item(Item::COMPOUND, 34, "Ink"));
		self::register(new Item(Item::COMPOUND, 35, "Hydrogen Peroxide"));
		self::register(new Item(Item::COMPOUND, 36, "Ammonia"));
		self::register(new Item(Item::COMPOUND, 37, "Sodium Hypochlorite"));
		self::register(new Item(Item::DIAMOND, 0, "Diamond"));
		self::register(new Item(Item::DRAGON_BREATH, 0, "Dragon's Breath"));
		self::register(new Item(Item::DYE, 0, "Ink Sac"));
		self::register(new Item(Item::DYE, 4, "Lapis Lazuli"));
		self::register(new Item(Item::EMERALD, 0, "Emerald"));
		self::register(new Item(Item::FEATHER, 0, "Feather"));
		self::register(new Item(Item::FERMENTED_SPIDER_EYE, 0, "Fermented Spider Eye"));
		self::register(new Item(Item::FLINT, 0, "Flint"));
		self::register(new Item(Item::GHAST_TEAR, 0, "Ghast Tear"));
		self::register(new Item(Item::GLISTERING_MELON, 0, "Glistering Melon"));
		self::register(new Item(Item::GLOWSTONE_DUST, 0, "Glowstone Dust"));
		self::register(new Item(Item::GOLD_INGOT, 0, "Gold Ingot"));
		self::register(new Item(Item::GOLD_NUGGET, 0, "Gold Nugget"));
		self::register(new Item(Item::GUNPOWDER, 0, "Gunpowder"));
		self::register(new Item(Item::HEART_OF_THE_SEA, 0, "Heart of the Sea"));
		self::register(new Item(Item::IRON_INGOT, 0, "Iron Ingot"));
		self::register(new Item(Item::IRON_NUGGET, 0, "Iron Nugget"));
		self::register(new Item(Item::LEATHER, 0, "Leather"));
		self::register(new Item(Item::MAGMA_CREAM, 0, "Magma Cream"));
		self::register(new Item(Item::NAUTILUS_SHELL, 0, "Nautilus Shell"));
		self::register(new Item(Item::NETHER_BRICK, 0, "Nether Brick"));
		self::register(new Item(Item::NETHER_QUARTZ, 0, "Nether Quartz"));
		self::register(new Item(Item::NETHER_STAR, 0, "Nether Star"));
		self::register(new Item(Item::PAPER, 0, "Paper"));
		self::register(new Item(Item::PRISMARINE_CRYSTALS, 0, "Prismarine Crystals"));
		self::register(new Item(Item::PRISMARINE_SHARD, 0, "Prismarine Shard"));
		self::register(new Item(Item::RABBIT_FOOT, 0, "Rabbit's Foot"));
		self::register(new Item(Item::RABBIT_HIDE, 0, "Rabbit Hide"));
		self::register(new Item(Item::SHULKER_SHELL, 0, "Shulker Shell"));
		self::register(new Item(Item::SLIME_BALL, 0, "Slimeball"));
		self::register(new Item(Item::SUGAR, 0, "Sugar"));
		self::register(new Item(Item::TURTLE_SHELL_PIECE, 0, "Scute"));
		self::register(new Item(Item::WHEAT, 0, "Wheat"));
		self::register(new ItemBlock(BlockLegacyIds::ACACIA_DOOR_BLOCK, 0, Item::ACACIA_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::BIRCH_DOOR_BLOCK, 0, Item::BIRCH_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::BREWING_STAND_BLOCK, 0, Item::BREWING_STAND));
		self::register(new ItemBlock(BlockLegacyIds::CAKE_BLOCK, 0, Item::CAKE));
		self::register(new ItemBlock(BlockLegacyIds::CAULDRON_BLOCK, 0, Item::CAULDRON));
		self::register(new ItemBlock(BlockLegacyIds::COMPARATOR_BLOCK, 0, Item::COMPARATOR));
		self::register(new ItemBlock(BlockLegacyIds::DARK_OAK_DOOR_BLOCK, 0, Item::DARK_OAK_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::FLOWER_POT_BLOCK, 0, Item::FLOWER_POT));
		self::register(new ItemBlock(BlockLegacyIds::HOPPER_BLOCK, 0, Item::HOPPER));
		self::register(new ItemBlock(BlockLegacyIds::IRON_DOOR_BLOCK, 0, Item::IRON_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::ITEM_FRAME_BLOCK, 0, Item::ITEM_FRAME));
		self::register(new ItemBlock(BlockLegacyIds::JUNGLE_DOOR_BLOCK, 0, Item::JUNGLE_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::NETHER_WART_PLANT, 0, Item::NETHER_WART));
		self::register(new ItemBlock(BlockLegacyIds::OAK_DOOR_BLOCK, 0, Item::OAK_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::REPEATER_BLOCK, 0, Item::REPEATER));
		self::register(new ItemBlock(BlockLegacyIds::SPRUCE_DOOR_BLOCK, 0, Item::SPRUCE_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::SUGARCANE_BLOCK, 0, Item::SUGARCANE));
		self::register(new Leggings(Item::CHAIN_LEGGINGS, 0, "Chainmail Leggings", new ArmorTypeInfo(4, 226)));
		self::register(new Leggings(Item::DIAMOND_LEGGINGS, 0, "Diamond Leggings", new ArmorTypeInfo(6, 496)));
		self::register(new Leggings(Item::GOLDEN_LEGGINGS, 0, "Gold Leggings", new ArmorTypeInfo(3, 106)));
		self::register(new Leggings(Item::IRON_LEGGINGS, 0, "Iron Leggings", new ArmorTypeInfo(5, 226)));
		self::register(new Leggings(Item::LEATHER_LEGGINGS, 0, "Leather Pants", new ArmorTypeInfo(2, 76)));
		//TODO: fix metadata for buckets with still liquid in them
		//the meta values are intentionally hardcoded because block IDs will change in the future
		self::register(new LiquidBucket(Item::BUCKET, 8, "Water Bucket", BlockLegacyIds::FLOWING_WATER));
		self::register(new LiquidBucket(Item::BUCKET, 10, "Lava Bucket", BlockLegacyIds::FLOWING_LAVA));
		self::register(new Melon(Item::MELON, 0, "Melon"));
		self::register(new MelonSeeds(Item::MELON_SEEDS, 0, "Melon Seeds"));
		self::register(new MilkBucket(Item::BUCKET, 1, "Milk Bucket"));
		self::register(new Minecart(Item::MINECART, 0, "Minecart"));
		self::register(new MushroomStew(Item::MUSHROOM_STEW, 0, "Mushroom Stew"));
		self::register(new PaintingItem(Item::PAINTING, 0, "Painting"));
		self::register(new Pickaxe(Item::DIAMOND_PICKAXE, "Diamond Pickaxe", TieredTool::TIER_DIAMOND));
		self::register(new Pickaxe(Item::GOLDEN_PICKAXE, "Gold Pickaxe", TieredTool::TIER_GOLD));
		self::register(new Pickaxe(Item::IRON_PICKAXE, "Iron Pickaxe", TieredTool::TIER_IRON));
		self::register(new Pickaxe(Item::STONE_PICKAXE, "Stone Pickaxe", TieredTool::TIER_STONE));
		self::register(new Pickaxe(Item::WOODEN_PICKAXE, "Wooden Pickaxe", TieredTool::TIER_WOODEN));
		self::register(new PoisonousPotato(Item::POISONOUS_POTATO, 0, "Poisonous Potato"));
		self::register(new Potato(Item::POTATO, 0, "Potato"));
		self::register(new Pufferfish(Item::PUFFERFISH, 0, "Pufferfish"));
		self::register(new PumpkinPie(Item::PUMPKIN_PIE, 0, "Pumpkin Pie"));
		self::register(new PumpkinSeeds(Item::PUMPKIN_SEEDS, 0, "Pumpkin Seeds"));
		self::register(new RabbitStew(Item::RABBIT_STEW, 0, "Rabbit Stew"));
		self::register(new RawBeef(Item::RAW_BEEF, 0, "Raw Beef"));
		self::register(new RawChicken(Item::RAW_CHICKEN, 0, "Raw Chicken"));
		self::register(new RawFish(Item::RAW_FISH, 0, "Raw Fish"));
		self::register(new RawMutton(Item::RAW_MUTTON, 0, "Raw Mutton"));
		self::register(new RawPorkchop(Item::RAW_PORKCHOP, 0, "Raw Porkchop"));
		self::register(new RawRabbit(Item::RAW_RABBIT, 0, "Raw Rabbit"));
		self::register(new RawSalmon(Item::RAW_SALMON, 0, "Raw Salmon"));
		self::register(new Redstone(Item::REDSTONE, 0, "Redstone"));
		self::register(new RottenFlesh(Item::ROTTEN_FLESH, 0, "Rotten Flesh"));
		self::register(new Shears(Item::SHEARS, 0, "Shears"));
		self::register(new Shovel(Item::DIAMOND_SHOVEL, "Diamond Shovel", TieredTool::TIER_DIAMOND));
		self::register(new Shovel(Item::GOLDEN_SHOVEL, "Gold Shovel", TieredTool::TIER_GOLD));
		self::register(new Shovel(Item::IRON_SHOVEL, "Iron Shovel", TieredTool::TIER_IRON));
		self::register(new Shovel(Item::STONE_SHOVEL, "Stone Shovel", TieredTool::TIER_STONE));
		self::register(new Shovel(Item::WOODEN_SHOVEL, "Wooden Shovel", TieredTool::TIER_WOODEN));
		self::register(new Sign(BlockLegacyIds::STANDING_SIGN, 0, Item::SIGN));
		self::register(new Sign(BlockLegacyIds::SPRUCE_STANDING_SIGN, 0, Item::SPRUCE_SIGN));
		self::register(new Sign(BlockLegacyIds::BIRCH_STANDING_SIGN, 0, Item::BIRCH_SIGN));
		self::register(new Sign(BlockLegacyIds::JUNGLE_STANDING_SIGN, 0, Item::JUNGLE_SIGN));
		self::register(new Sign(BlockLegacyIds::ACACIA_STANDING_SIGN, 0, Item::ACACIA_SIGN));
		self::register(new Sign(BlockLegacyIds::DARKOAK_STANDING_SIGN, 0, Item::DARKOAK_SIGN));
		self::register(new Snowball(Item::SNOWBALL, 0, "Snowball"));
		self::register(new SpiderEye(Item::SPIDER_EYE, 0, "Spider Eye"));
		self::register(new Steak(Item::STEAK, 0, "Steak"));
		self::register(new Stick(Item::STICK, 0, "Stick"));
		self::register(new StringItem(Item::STRING, 0, "String"));
		self::register(new Sword(Item::DIAMOND_SWORD, "Diamond Sword", TieredTool::TIER_DIAMOND));
		self::register(new Sword(Item::GOLDEN_SWORD, "Gold Sword", TieredTool::TIER_GOLD));
		self::register(new Sword(Item::IRON_SWORD, "Iron Sword", TieredTool::TIER_IRON));
		self::register(new Sword(Item::STONE_SWORD, "Stone Sword", TieredTool::TIER_STONE));
		self::register(new Sword(Item::WOODEN_SWORD, "Wooden Sword", TieredTool::TIER_WOODEN));
		self::register(new Totem(Item::TOTEM, 0, "Totem of Undying"));
		self::register(new WheatSeeds(Item::WHEAT_SEEDS, 0, "Wheat Seeds"));
		self::register(new WritableBook(Item::WRITABLE_BOOK, 0, "Book & Quill"));
		self::register(new WrittenBook(Item::WRITTEN_BOOK, 0, "Written Book"));

		foreach(SkullType::getAll() as $skullType){
			self::register(new Skull(Item::SKULL, $skullType->getMagicNumber(), $skullType->getDisplayName(), $skullType));
		}

		/** @var int[]|\SplObjectStorage $dyeMap */
		$dyeMap = new \SplObjectStorage();
		$dyeMap[DyeColor::BLACK()] = 16;
		$dyeMap[DyeColor::BROWN()] = 17;
		$dyeMap[DyeColor::BLUE()] = 18;
		$dyeMap[DyeColor::WHITE()] = 19;
		foreach(DyeColor::getAll() as $color){
			//TODO: use colour object directly
			//TODO: add interface to dye-colour objects
			self::register(new Dye(Item::DYE, $dyeMap[$color] ?? $color->getInvertedMagicNumber(), $color->getDisplayName() . " Dye", $color));
			self::register(new Bed(Item::BED, $color->getMagicNumber(), $color->getDisplayName() . " Bed", $color));
			self::register(new Banner(Item::BANNER, $color->getInvertedMagicNumber(), $color->getDisplayName() . " Banner", $color));
		}

		foreach(Potion::ALL as $type){
			self::register(new Potion(Item::POTION, $type, "Potion"));
			self::register(new SplashPotion(Item::SPLASH_POTION, $type, "Splash Potion"));
		}

		foreach(EntityFactory::getKnownTypes() as $className){
			/** @var Living|string $className */
			if(is_a($className, Living::class, true) and $className::NETWORK_ID !== -1){
				self::register(new SpawnEgg(Item::SPAWN_EGG, $className::NETWORK_ID, "Spawn Egg", $className));
			}
		}

		foreach(TreeType::getAll() as $type){
			self::register(new Boat(Item::BOAT, $type->getMagicNumber(), $type->getDisplayName() . " Boat", $type));
		}

		//region --- auto-generated TODOs ---
		//TODO: minecraft:armor_stand
		//TODO: minecraft:balloon
		//TODO: minecraft:banner_pattern
		//TODO: minecraft:campfire
		//TODO: minecraft:carrotOnAStick
		//TODO: minecraft:chest_minecart
		//TODO: minecraft:command_block_minecart
		//TODO: minecraft:crossbow
		//TODO: minecraft:elytra
		//TODO: minecraft:emptyMap
		//TODO: minecraft:enchanted_book
		//TODO: minecraft:end_crystal
		//TODO: minecraft:ender_eye
		//TODO: minecraft:fireball
		//TODO: minecraft:fireworks
		//TODO: minecraft:fireworksCharge
		//TODO: minecraft:glow_stick
		//TODO: minecraft:hopper_minecart
		//TODO: minecraft:horsearmordiamond
		//TODO: minecraft:horsearmorgold
		//TODO: minecraft:horsearmoriron
		//TODO: minecraft:horsearmorleather
		//TODO: minecraft:ice_bomb
		//TODO: minecraft:kelp
		//TODO: minecraft:lead
		//TODO: minecraft:lingering_potion
		//TODO: minecraft:map
		//TODO: minecraft:medicine
		//TODO: minecraft:name_tag
		//TODO: minecraft:phantom_membrane
		//TODO: minecraft:rapid_fertilizer
		//TODO: minecraft:record_11
		//TODO: minecraft:record_13
		//TODO: minecraft:record_blocks
		//TODO: minecraft:record_cat
		//TODO: minecraft:record_chirp
		//TODO: minecraft:record_far
		//TODO: minecraft:record_mall
		//TODO: minecraft:record_mellohi
		//TODO: minecraft:record_stal
		//TODO: minecraft:record_strad
		//TODO: minecraft:record_wait
		//TODO: minecraft:record_ward
		//TODO: minecraft:saddle
		//TODO: minecraft:shield
		//TODO: minecraft:sparkler
		//TODO: minecraft:spawn_egg
		//TODO: minecraft:sweet_berries
		//TODO: minecraft:tnt_minecart
		//TODO: minecraft:trident
		//TODO: minecraft:turtle_helmet
		//endregion
	}

	/**
	 * Registers an item type into the index. Plugins may use this method to register new item types or override existing
	 * ones.
	 *
	 * NOTE: If you are registering a new item type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param Item $item
	 * @param bool $override
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered item without specifying the
	 * $override parameter.
	 */
	public static function register(Item $item, bool $override = false) : void{
		$id = $item->getId();
		$variant = $item->getMeta();

		if(!$override and self::isRegistered($id, $variant)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		self::$list[self::getListOffset($id, $variant)] = clone $item;
	}

	/**
	 * Returns an instance of the Item with the specified id, meta, count and NBT.
	 *
	 * @param int              $id
	 * @param int              $meta
	 * @param int              $count
	 * @param CompoundTag|null $tags
	 *
	 * @return Item
	 * @throws \InvalidArgumentException
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, ?CompoundTag $tags = null) : Item{
		/** @var Item $item */
		$item = null;
		if($meta !== -1){
			if(isset(self::$list[$offset = self::getListOffset($id, $meta)])){
				$item = clone self::$list[$offset];
			}elseif(isset(self::$list[$zero = self::getListOffset($id, 0)]) and self::$list[$zero] instanceof Durable){
				/** @var Durable $item */
				$item = clone self::$list[$zero];
				$item->setDamage($meta);
			}elseif($id < 256){ //intentionally includes negatives, for extended block IDs
				$item = new ItemBlock($id, $meta);
			}
		}

		if($item === null){
			//negative damage values will fallthru to here, to avoid crazy shit with crafting wildcard hacks
			$item = new Item($id, $meta);
		}

		$item->setCount($count);
		if($tags !== null){
			$item->setNamedTag($tags);
		}
		return $item;
	}

	/**
	 * Tries to parse the specified string into Item types.
	 *
	 * Example accepted formats:
	 * - `diamond_pickaxe:5`
	 * - `minecraft:string`
	 * - `351:4 (lapis lazuli ID:meta)`
	 *
	 * @param string $str
	 *
	 * @return Item
	 *
	 * @throws \InvalidArgumentException if the given string cannot be parsed as an item identifier
	 */
	public static function fromString(string $str) : Item{
		$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
		if(!isset($b[1])){
			$meta = 0;
		}elseif(is_numeric($b[1])){
			$meta = (int) $b[1];
		}else{
			throw new \InvalidArgumentException("Unable to parse \"" . $b[1] . "\" from \"" . $str . "\" as a valid meta value");
		}

		if(is_numeric($b[0])){
			$item = self::get((int) $b[0], $meta);
		}elseif(defined(ItemIds::class . "::" . strtoupper($b[0]))){
			$item = self::get(constant(ItemIds::class . "::" . strtoupper($b[0])), $meta);
		}else{
			throw new \InvalidArgumentException("Unable to resolve \"" . $str . "\" to a valid item");
		}

		return $item;
	}

	public static function air() : Item{
		return self::$air ?? (self::$air = self::get(ItemIds::AIR, 0, 0));
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 *
	 * @param int $id
	 * @param int $variant
	 *
	 * @return bool
	 */
	public static function isRegistered(int $id, int $variant = 0) : bool{
		if($id < 256){
			return BlockFactory::isRegistered($id);
		}

		return isset(self::$list[self::getListOffset($id, $variant)]);
	}

	private static function getListOffset(int $id, int $variant) : int{
		if($id < -0x8000 or $id > 0x7fff){
			throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		return (($id & 0xffff) << 16) | ($variant & 0xffff);
	}

	public static function getAllRegistered() : array{
		return self::$list;
	}
}
