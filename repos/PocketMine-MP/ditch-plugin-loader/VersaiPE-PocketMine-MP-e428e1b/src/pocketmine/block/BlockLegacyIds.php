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

namespace pocketmine\block;

interface BlockLegacyIds{

	public const AIR = 0;
	public const STONE = 1;
	public const GRASS = 2;
	public const DIRT = 3;
	public const COBBLESTONE = 4;
	public const PLANKS = 5, WOODEN_PLANKS = 5;
	public const SAPLING = 6;
	public const BEDROCK = 7;
	public const FLOWING_WATER = 8;
	public const STILL_WATER = 9, WATER = 9;
	public const FLOWING_LAVA = 10;
	public const LAVA = 11, STILL_LAVA = 11;
	public const SAND = 12;
	public const GRAVEL = 13;
	public const GOLD_ORE = 14;
	public const IRON_ORE = 15;
	public const COAL_ORE = 16;
	public const LOG = 17;
	public const LEAVES = 18;
	public const SPONGE = 19;
	public const GLASS = 20;
	public const LAPIS_ORE = 21;
	public const LAPIS_BLOCK = 22;
	public const DISPENSER = 23;
	public const SANDSTONE = 24;
	public const NOTEBLOCK = 25, NOTE_BLOCK = 25;
	public const BED_BLOCK = 26;
	public const GOLDEN_RAIL = 27, POWERED_RAIL = 27;
	public const DETECTOR_RAIL = 28;
	public const STICKY_PISTON = 29;
	public const COBWEB = 30, WEB = 30;
	public const TALLGRASS = 31, TALL_GRASS = 31;
	public const DEADBUSH = 32, DEAD_BUSH = 32;
	public const PISTON = 33;
	public const PISTONARMCOLLISION = 34, PISTON_ARM_COLLISION = 34;
	public const WOOL = 35;
	public const ELEMENT_0 = 36;
	public const DANDELION = 37, YELLOW_FLOWER = 37;
	public const POPPY = 38, RED_FLOWER = 38;
	public const BROWN_MUSHROOM = 39;
	public const RED_MUSHROOM = 40;
	public const GOLD_BLOCK = 41;
	public const IRON_BLOCK = 42;
	public const DOUBLE_STONE_SLAB = 43;
	public const STONE_SLAB = 44;
	public const BRICK_BLOCK = 45;
	public const TNT = 46;
	public const BOOKSHELF = 47;
	public const MOSSY_COBBLESTONE = 48, MOSS_STONE = 48;
	public const OBSIDIAN = 49;
	public const TORCH = 50;
	public const FIRE = 51;
	public const MOB_SPAWNER = 52, MONSTER_SPAWNER = 52;
	public const OAK_STAIRS = 53, WOODEN_STAIRS = 53;
	public const CHEST = 54;
	public const REDSTONE_WIRE = 55;
	public const DIAMOND_ORE = 56;
	public const DIAMOND_BLOCK = 57;
	public const CRAFTING_TABLE = 58, WORKBENCH = 58;
	public const WHEAT_BLOCK = 59;
	public const FARMLAND = 60;
	public const FURNACE = 61;
	public const BURNING_FURNACE = 62, LIT_FURNACE = 62;
	public const SIGN_POST = 63, STANDING_SIGN = 63;
	public const OAK_DOOR_BLOCK = 64, WOODEN_DOOR_BLOCK = 64;
	public const LADDER = 65;
	public const RAIL = 66;
	public const COBBLESTONE_STAIRS = 67, STONE_STAIRS = 67;
	public const WALL_SIGN = 68;
	public const LEVER = 69;
	public const STONE_PRESSURE_PLATE = 70;
	public const IRON_DOOR_BLOCK = 71;
	public const WOODEN_PRESSURE_PLATE = 72;
	public const REDSTONE_ORE = 73;
	public const GLOWING_REDSTONE_ORE = 74, LIT_REDSTONE_ORE = 74;
	public const UNLIT_REDSTONE_TORCH = 75;
	public const LIT_REDSTONE_TORCH = 76, REDSTONE_TORCH = 76;
	public const STONE_BUTTON = 77;
	public const SNOW_LAYER = 78;
	public const ICE = 79;
	public const SNOW = 80, SNOW_BLOCK = 80;
	public const CACTUS = 81;
	public const CLAY_BLOCK = 82;
	public const REEDS_BLOCK = 83, SUGARCANE_BLOCK = 83;
	public const JUKEBOX = 84;
	public const FENCE = 85;
	public const PUMPKIN = 86;
	public const NETHERRACK = 87;
	public const SOUL_SAND = 88;
	public const GLOWSTONE = 89;
	public const PORTAL = 90;
	public const JACK_O_LANTERN = 91, LIT_PUMPKIN = 91;
	public const CAKE_BLOCK = 92;
	public const REPEATER_BLOCK = 93, UNPOWERED_REPEATER = 93;
	public const POWERED_REPEATER = 94;
	public const INVISIBLEBEDROCK = 95, INVISIBLE_BEDROCK = 95;
	public const TRAPDOOR = 96, WOODEN_TRAPDOOR = 96;
	public const MONSTER_EGG = 97;
	public const STONEBRICK = 98, STONE_BRICK = 98, STONE_BRICKS = 98;
	public const BROWN_MUSHROOM_BLOCK = 99;
	public const RED_MUSHROOM_BLOCK = 100;
	public const IRON_BARS = 101;
	public const GLASS_PANE = 102;
	public const MELON_BLOCK = 103;
	public const PUMPKIN_STEM = 104;
	public const MELON_STEM = 105;
	public const VINE = 106, VINES = 106;
	public const FENCE_GATE = 107, OAK_FENCE_GATE = 107;
	public const BRICK_STAIRS = 108;
	public const STONE_BRICK_STAIRS = 109;
	public const MYCELIUM = 110;
	public const LILY_PAD = 111, WATERLILY = 111, WATER_LILY = 111;
	public const NETHER_BRICK_BLOCK = 112;
	public const NETHER_BRICK_FENCE = 113;
	public const NETHER_BRICK_STAIRS = 114;
	public const NETHER_WART_PLANT = 115;
	public const ENCHANTING_TABLE = 116, ENCHANTMENT_TABLE = 116;
	public const BREWING_STAND_BLOCK = 117;
	public const CAULDRON_BLOCK = 118;
	public const END_PORTAL = 119;
	public const END_PORTAL_FRAME = 120;
	public const END_STONE = 121;
	public const DRAGON_EGG = 122;
	public const REDSTONE_LAMP = 123;
	public const LIT_REDSTONE_LAMP = 124;
	public const DROPPER = 125;
	public const ACTIVATOR_RAIL = 126;
	public const COCOA = 127, COCOA_BLOCK = 127;
	public const SANDSTONE_STAIRS = 128;
	public const EMERALD_ORE = 129;
	public const ENDER_CHEST = 130;
	public const TRIPWIRE_HOOK = 131;
	public const TRIPWIRE = 132, TRIP_WIRE = 132;
	public const EMERALD_BLOCK = 133;
	public const SPRUCE_STAIRS = 134;
	public const BIRCH_STAIRS = 135;
	public const JUNGLE_STAIRS = 136;
	public const COMMAND_BLOCK = 137;
	public const BEACON = 138;
	public const COBBLESTONE_WALL = 139, STONE_WALL = 139;
	public const FLOWER_POT_BLOCK = 140;
	public const CARROTS = 141, CARROT_BLOCK = 141;
	public const POTATOES = 142, POTATO_BLOCK = 142;
	public const WOODEN_BUTTON = 143;
	public const MOB_HEAD_BLOCK = 144, SKULL_BLOCK = 144;
	public const ANVIL = 145;
	public const TRAPPED_CHEST = 146;
	public const LIGHT_WEIGHTED_PRESSURE_PLATE = 147;
	public const HEAVY_WEIGHTED_PRESSURE_PLATE = 148;
	public const COMPARATOR_BLOCK = 149, UNPOWERED_COMPARATOR = 149;
	public const POWERED_COMPARATOR = 150;
	public const DAYLIGHT_DETECTOR = 151, DAYLIGHT_SENSOR = 151;
	public const REDSTONE_BLOCK = 152;
	public const NETHER_QUARTZ_ORE = 153, QUARTZ_ORE = 153;
	public const HOPPER_BLOCK = 154;
	public const QUARTZ_BLOCK = 155;
	public const QUARTZ_STAIRS = 156;
	public const DOUBLE_WOODEN_SLAB = 157;
	public const WOODEN_SLAB = 158;
	public const STAINED_CLAY = 159, STAINED_HARDENED_CLAY = 159, TERRACOTTA = 159;
	public const STAINED_GLASS_PANE = 160;
	public const LEAVES2 = 161;
	public const LOG2 = 162;
	public const ACACIA_STAIRS = 163;
	public const DARK_OAK_STAIRS = 164;
	public const SLIME = 165, SLIME_BLOCK = 165;
	public const GLOW_STICK = 166;
	public const IRON_TRAPDOOR = 167;
	public const PRISMARINE = 168;
	public const SEALANTERN = 169, SEA_LANTERN = 169;
	public const HAY_BALE = 170, HAY_BLOCK = 170;
	public const CARPET = 171;
	public const HARDENED_CLAY = 172;
	public const COAL_BLOCK = 173;
	public const PACKED_ICE = 174;
	public const DOUBLE_PLANT = 175;
	public const STANDING_BANNER = 176;
	public const WALL_BANNER = 177;
	public const DAYLIGHT_DETECTOR_INVERTED = 178, DAYLIGHT_SENSOR_INVERTED = 178;
	public const RED_SANDSTONE = 179;
	public const RED_SANDSTONE_STAIRS = 180;
	public const DOUBLE_STONE_SLAB2 = 181;
	public const STONE_SLAB2 = 182;
	public const SPRUCE_FENCE_GATE = 183;
	public const BIRCH_FENCE_GATE = 184;
	public const JUNGLE_FENCE_GATE = 185;
	public const DARK_OAK_FENCE_GATE = 186;
	public const ACACIA_FENCE_GATE = 187;
	public const REPEATING_COMMAND_BLOCK = 188;
	public const CHAIN_COMMAND_BLOCK = 189;
	public const HARD_GLASS_PANE = 190;
	public const HARD_STAINED_GLASS_PANE = 191;
	public const CHEMICAL_HEAT = 192;
	public const SPRUCE_DOOR_BLOCK = 193;
	public const BIRCH_DOOR_BLOCK = 194;
	public const JUNGLE_DOOR_BLOCK = 195;
	public const ACACIA_DOOR_BLOCK = 196;
	public const DARK_OAK_DOOR_BLOCK = 197;
	public const GRASS_PATH = 198;
	public const FRAME_BLOCK = 199, ITEM_FRAME_BLOCK = 199;
	public const CHORUS_FLOWER = 200;
	public const PURPUR_BLOCK = 201;
	public const COLORED_TORCH_RG = 202;
	public const PURPUR_STAIRS = 203;
	public const COLORED_TORCH_BP = 204;
	public const UNDYED_SHULKER_BOX = 205;
	public const END_BRICKS = 206;
	public const FROSTED_ICE = 207;
	public const END_ROD = 208;
	public const END_GATEWAY = 209;

	public const MAGMA = 213;
	public const NETHER_WART_BLOCK = 214;
	public const RED_NETHER_BRICK = 215;
	public const BONE_BLOCK = 216;

	public const SHULKER_BOX = 218;
	public const PURPLE_GLAZED_TERRACOTTA = 219;
	public const WHITE_GLAZED_TERRACOTTA = 220;
	public const ORANGE_GLAZED_TERRACOTTA = 221;
	public const MAGENTA_GLAZED_TERRACOTTA = 222;
	public const LIGHT_BLUE_GLAZED_TERRACOTTA = 223;
	public const YELLOW_GLAZED_TERRACOTTA = 224;
	public const LIME_GLAZED_TERRACOTTA = 225;
	public const PINK_GLAZED_TERRACOTTA = 226;
	public const GRAY_GLAZED_TERRACOTTA = 227;
	public const SILVER_GLAZED_TERRACOTTA = 228;
	public const CYAN_GLAZED_TERRACOTTA = 229;

	public const BLUE_GLAZED_TERRACOTTA = 231;
	public const BROWN_GLAZED_TERRACOTTA = 232;
	public const GREEN_GLAZED_TERRACOTTA = 233;
	public const RED_GLAZED_TERRACOTTA = 234;
	public const BLACK_GLAZED_TERRACOTTA = 235;
	public const CONCRETE = 236;
	public const CONCRETEPOWDER = 237, CONCRETE_POWDER = 237;
	public const CHEMISTRY_TABLE = 238;
	public const UNDERWATER_TORCH = 239;
	public const CHORUS_PLANT = 240;
	public const STAINED_GLASS = 241;

	public const PODZOL = 243;
	public const BEETROOT_BLOCK = 244;
	public const STONECUTTER = 245;
	public const GLOWINGOBSIDIAN = 246, GLOWING_OBSIDIAN = 246;
	public const NETHERREACTOR = 247, NETHER_REACTOR = 247;
	public const INFO_UPDATE = 248;
	public const INFO_UPDATE2 = 249;
	public const MOVINGBLOCK = 250, MOVING_BLOCK = 250;
	public const OBSERVER = 251;
	public const STRUCTURE_BLOCK = 252;
	public const HARD_GLASS = 253;
	public const HARD_STAINED_GLASS = 254;
	public const RESERVED6 = 255;

	public const PRISMARINE_STAIRS = 257;
	public const DARK_PRISMARINE_STAIRS = 258;
	public const PRISMARINE_BRICKS_STAIRS = 259;
	public const STRIPPED_SPRUCE_LOG = 260;
	public const STRIPPED_BIRCH_LOG = 261;
	public const STRIPPED_JUNGLE_LOG = 262;
	public const STRIPPED_ACACIA_LOG = 263;
	public const STRIPPED_DARK_OAK_LOG = 264;
	public const STRIPPED_OAK_LOG = 265;
	public const BLUE_ICE = 266;
	public const ELEMENT_1 = 267;
	public const ELEMENT_2 = 268;
	public const ELEMENT_3 = 269;
	public const ELEMENT_4 = 270;
	public const ELEMENT_5 = 271;
	public const ELEMENT_6 = 272;
	public const ELEMENT_7 = 273;
	public const ELEMENT_8 = 274;
	public const ELEMENT_9 = 275;
	public const ELEMENT_10 = 276;
	public const ELEMENT_11 = 277;
	public const ELEMENT_12 = 278;
	public const ELEMENT_13 = 279;
	public const ELEMENT_14 = 280;
	public const ELEMENT_15 = 281;
	public const ELEMENT_16 = 282;
	public const ELEMENT_17 = 283;
	public const ELEMENT_18 = 284;
	public const ELEMENT_19 = 285;
	public const ELEMENT_20 = 286;
	public const ELEMENT_21 = 287;
	public const ELEMENT_22 = 288;
	public const ELEMENT_23 = 289;
	public const ELEMENT_24 = 290;
	public const ELEMENT_25 = 291;
	public const ELEMENT_26 = 292;
	public const ELEMENT_27 = 293;
	public const ELEMENT_28 = 294;
	public const ELEMENT_29 = 295;
	public const ELEMENT_30 = 296;
	public const ELEMENT_31 = 297;
	public const ELEMENT_32 = 298;
	public const ELEMENT_33 = 299;
	public const ELEMENT_34 = 300;
	public const ELEMENT_35 = 301;
	public const ELEMENT_36 = 302;
	public const ELEMENT_37 = 303;
	public const ELEMENT_38 = 304;
	public const ELEMENT_39 = 305;
	public const ELEMENT_40 = 306;
	public const ELEMENT_41 = 307;
	public const ELEMENT_42 = 308;
	public const ELEMENT_43 = 309;
	public const ELEMENT_44 = 310;
	public const ELEMENT_45 = 311;
	public const ELEMENT_46 = 312;
	public const ELEMENT_47 = 313;
	public const ELEMENT_48 = 314;
	public const ELEMENT_49 = 315;
	public const ELEMENT_50 = 316;
	public const ELEMENT_51 = 317;
	public const ELEMENT_52 = 318;
	public const ELEMENT_53 = 319;
	public const ELEMENT_54 = 320;
	public const ELEMENT_55 = 321;
	public const ELEMENT_56 = 322;
	public const ELEMENT_57 = 323;
	public const ELEMENT_58 = 324;
	public const ELEMENT_59 = 325;
	public const ELEMENT_60 = 326;
	public const ELEMENT_61 = 327;
	public const ELEMENT_62 = 328;
	public const ELEMENT_63 = 329;
	public const ELEMENT_64 = 330;
	public const ELEMENT_65 = 331;
	public const ELEMENT_66 = 332;
	public const ELEMENT_67 = 333;
	public const ELEMENT_68 = 334;
	public const ELEMENT_69 = 335;
	public const ELEMENT_70 = 336;
	public const ELEMENT_71 = 337;
	public const ELEMENT_72 = 338;
	public const ELEMENT_73 = 339;
	public const ELEMENT_74 = 340;
	public const ELEMENT_75 = 341;
	public const ELEMENT_76 = 342;
	public const ELEMENT_77 = 343;
	public const ELEMENT_78 = 344;
	public const ELEMENT_79 = 345;
	public const ELEMENT_80 = 346;
	public const ELEMENT_81 = 347;
	public const ELEMENT_82 = 348;
	public const ELEMENT_83 = 349;
	public const ELEMENT_84 = 350;
	public const ELEMENT_85 = 351;
	public const ELEMENT_86 = 352;
	public const ELEMENT_87 = 353;
	public const ELEMENT_88 = 354;
	public const ELEMENT_89 = 355;
	public const ELEMENT_90 = 356;
	public const ELEMENT_91 = 357;
	public const ELEMENT_92 = 358;
	public const ELEMENT_93 = 359;
	public const ELEMENT_94 = 360;
	public const ELEMENT_95 = 361;
	public const ELEMENT_96 = 362;
	public const ELEMENT_97 = 363;
	public const ELEMENT_98 = 364;
	public const ELEMENT_99 = 365;
	public const ELEMENT_100 = 366;
	public const ELEMENT_101 = 367;
	public const ELEMENT_102 = 368;
	public const ELEMENT_103 = 369;
	public const ELEMENT_104 = 370;
	public const ELEMENT_105 = 371;
	public const ELEMENT_106 = 372;
	public const ELEMENT_107 = 373;
	public const ELEMENT_108 = 374;
	public const ELEMENT_109 = 375;
	public const ELEMENT_110 = 376;
	public const ELEMENT_111 = 377;
	public const ELEMENT_112 = 378;
	public const ELEMENT_113 = 379;
	public const ELEMENT_114 = 380;
	public const ELEMENT_115 = 381;
	public const ELEMENT_116 = 382;
	public const ELEMENT_117 = 383;
	public const ELEMENT_118 = 384;
	public const SEAGRASS = 385;
	public const CORAL = 386;
	public const CORAL_BLOCK = 387;
	public const CORAL_FAN = 388;
	public const CORAL_FAN_DEAD = 389;
	public const CORAL_FAN_HANG = 390;
	public const CORAL_FAN_HANG2 = 391;
	public const CORAL_FAN_HANG3 = 392;
	public const KELP = 393;
	public const DRIED_KELP_BLOCK = 394;
	public const ACACIA_BUTTON = 395;
	public const BIRCH_BUTTON = 396;
	public const DARK_OAK_BUTTON = 397;
	public const JUNGLE_BUTTON = 398;
	public const SPRUCE_BUTTON = 399;
	public const ACACIA_TRAPDOOR = 400;
	public const BIRCH_TRAPDOOR = 401;
	public const DARK_OAK_TRAPDOOR = 402;
	public const JUNGLE_TRAPDOOR = 403;
	public const SPRUCE_TRAPDOOR = 404;
	public const ACACIA_PRESSURE_PLATE = 405;
	public const BIRCH_PRESSURE_PLATE = 406;
	public const DARK_OAK_PRESSURE_PLATE = 407;
	public const JUNGLE_PRESSURE_PLATE = 408;
	public const SPRUCE_PRESSURE_PLATE = 409;
	public const CARVED_PUMPKIN = 410;
	public const SEA_PICKLE = 411;
	public const CONDUIT = 412;

	public const TURTLE_EGG = 414;
	public const BUBBLE_COLUMN = 415;
	public const BARRIER = 416;
	public const STONE_SLAB3 = 417;
	public const BAMBOO = 418;
	public const BAMBOO_SAPLING = 419;
	public const SCAFFOLDING = 420;
	public const STONE_SLAB4 = 421;
	public const DOUBLE_STONE_SLAB3 = 422;
	public const DOUBLE_STONE_SLAB4 = 423;
	public const GRANITE_STAIRS = 424;
	public const DIORITE_STAIRS = 425;
	public const ANDESITE_STAIRS = 426;
	public const POLISHED_GRANITE_STAIRS = 427;
	public const POLISHED_DIORITE_STAIRS = 428;
	public const POLISHED_ANDESITE_STAIRS = 429;
	public const MOSSY_STONE_BRICK_STAIRS = 430;
	public const SMOOTH_RED_SANDSTONE_STAIRS = 431;
	public const SMOOTH_SANDSTONE_STAIRS = 432;
	public const END_BRICK_STAIRS = 433;
	public const MOSSY_COBBLESTONE_STAIRS = 434;
	public const NORMAL_STONE_STAIRS = 435;
	public const SPRUCE_STANDING_SIGN = 436;
	public const SPRUCE_WALL_SIGN = 437;
	public const SMOOTH_STONE = 438;
	public const RED_NETHER_BRICK_STAIRS = 439;
	public const SMOOTH_QUARTZ_STAIRS = 440;
	public const BIRCH_STANDING_SIGN = 441;
	public const BIRCH_WALL_SIGN = 442;
	public const JUNGLE_STANDING_SIGN = 443;
	public const JUNGLE_WALL_SIGN = 444;
	public const ACACIA_STANDING_SIGN = 445;
	public const ACACIA_WALL_SIGN = 446;
	public const DARKOAK_STANDING_SIGN = 447;
	public const DARKOAK_WALL_SIGN = 448;
	public const LECTERN = 449;
	public const GRINDSTONE = 450;
	public const BLAST_FURNACE = 451;
	public const STONECUTTER_BLOCK = 452;
	public const SMOKER = 453;

	public const CARTOGRAPHY_TABLE = 455;
	public const FLETCHING_TABLE = 456;
	public const SMITHING_TABLE = 457;
	public const BARREL = 458;
	public const LOOM = 459;

	public const BELL = 461;
	public const SWEET_BERRY_BUSH = 462;
	public const LANTERN = 463;
	public const CAMPFIRE = 464;
	public const LAVA_CAULDRON = 465;
	public const JIGSAW = 466;
	public const WOOD = 467;
	public const COMPOSTER = 468;

}
