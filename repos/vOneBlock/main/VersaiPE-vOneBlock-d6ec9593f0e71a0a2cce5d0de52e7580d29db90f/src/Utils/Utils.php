<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Utils;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\StringToItemParser;
use Versai\OneBlock\Main;
use pocketmine\network\mcpe\protocol\types\BossBarColor;

class Utils {

	/**
	 * @description This function should be used to translate config strings like "glass pane" to the vanilla blocks format of "GLASS_PANE"
	 */
	public static function translateStringToBlock(string $item): Block {
		$item = strtoupper($item);
		$item = str_replace(" ", "_", $item);
		$itemReal = StringToItemParser::getInstance()->parse($item);
		if (!$itemReal) {
			Main::getInstance()->getLogger()->warning("Block " . $item . " not found in VanillaBlocks");
			return VanillaBlocks::DIRT();
		}
		$itemReal = $itemReal->getBlock();
		if (!$itemReal instanceof Air) {
			return $itemReal;
		} else {
			Main::getInstance()->getLogger()->warning("Item {$item} was not found");
			return VanillaBlocks::DIRT();
		}
	}

	public static function getRandomBlockFromConfig(int $tier): string {
		$blocks = Main::getInstance()->getConfig()->getNested("levels.{$tier}");
		if (!$blocks) {
			$blocks = Main::getInstance()->getConfig()->getNested("levels." . self::getHighestLevel());
		}
		$getBlocks = [];
		$set = [];
		foreach($blocks as $block => $percent) {
			$getBlocks[] = $block;
			$set[] = (int)$percent/100;
		}
		$num = self::checkWithSet($set) ?? 0;
		return $getBlocks[$num];
	}

	public static function getHighestLevel(): int {
		return count(Main::getInstance()->getConfig()->get("levels")) - 1;
	}

	# https://stackoverflow.com/questions/21572363/generate-random-numbers-with-fix-probability
	private static function checkWithSet(array $set, $length=10000) {
		$left = 0;
		foreach($set as $num=>$right)
		{
			$set[$num] = $left + $right*$length;
			$left = $set[$num];
		}
		$test = mt_rand(1, $length);
		$left = 1;
		foreach($set as $num=>$right)
		{
			if($test>=$left && $test<=$right)
			{
				return $num;
			}
			$left = $right;
		}
		return null;//debug, no event realized
	}

	/**
	 * Get the color, that corresponds to the players percentage to completion
	 * between 0-1
	 */
	public static function getBossBarColorFromPercentage(float $percentage): BossBarColor {
		match ($percentage) {
			// Between 0% and 33%
			0.0 < $percentage && $percentage < 0.33 => BossBarColor::RED,
			// Between 34% and 66%
			0.34 < $percentage && $percentage < 0.66 => BossBarColor::YELLOW,
			// 67% <
			0.67 < $percentage => BossBarColor::GREEN,
			default => BossBarColor::RED 
		};
	}
}