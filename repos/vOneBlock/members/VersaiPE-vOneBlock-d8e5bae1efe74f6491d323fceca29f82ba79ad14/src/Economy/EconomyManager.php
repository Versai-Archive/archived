<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Economy;

use pocketmine\block\Air;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use Versai\OneBlock\Events\Economy\SellEvent;
use Versai\OneBlock\Main;
use Versai\OneBlock\Translator\Translator;

class EconomyManager {

	public static function sellItemsInInventory(Player $player) {
		$session = Main::getInstance()->getSessionManager()->getSession($player);
		if (!$session) {
			$player->sendMessage(Translator::translate("errors.session.not_found"));
		}
		$transaction = [];
		$val = 0;
		foreach ($player->getInventory()->getContents() as $slot => $item) {
			if ($item instanceof Air) {
				continue;
			}
			$str = strtolower($item->getVanillaName());
			var_dump($str);
			$worth = Main::getInstance()->getConfig()->getNested("sell.{$str}");
			if (!$worth) {
				continue;
			}
			$count = $item->getCount();
			if (isset($transaction[$item->getVanillaName()])) {
				$transaction[$item->getVanillaName()] += $item->getCount();
				$val += $worth * $count;
				$player->getInventory()->setItem($slot, VanillaBlocks::AIR()->asItem());
				$session->addCoins($worth * $count);
				continue;
			}
			$transaction[$item->getVanillaName()] = $item->getCount();
			$val += $worth * $count;
			$player->getInventory()->setItem($slot, VanillaBlocks::AIR()->asItem());
			$session->addCoins($worth * $count);
		}
		(new SellEvent($player, $transaction, $val))->call();
	}

	/**
	 * @param Player $player
	 * @return Item[]
	 */
	public static function getSellableItems(Player $player): array {
		$itemsBase = $player->getInventory()->getContents();
		$items = [];
		foreach($itemsBase as $slot => $item) {
			$str = strtolower($item->getVanillaName());
			$worth = $item->getCount() * Main::getInstance()->getConfig()->getNested("sell.{$str}");
			if (!$worth) {
				continue;
			}
			$items[] = $item;
		}
		return $items;
	}

	public static function getItemPrice(Item $item): ?float {
		$str = strtolower($item->getVanillaName());
		$worth = Main::getInstance()->getConfig()->getNested("sell.{$str}");
		if (!$worth) {
			return null;
		}
		return (float)$worth;
	}

	public static function sellItemInHand(Player $player): void {
		$session = Main::getInstance()->getSessionManager()->getSession($player);
		if (!$session) {
			$player->sendMessage(Translator::translate("errors.session.not_found"));
		}
		$item = $player->getInventory()->getItemInHand();
		if ($player->getInventory()->getItemInHand() instanceof Air) {
			return;
		}
		$str = strtolower($item->getVanillaName());
		$worth = Main::getInstance()->getConfig()->getNested("sell.{$str}");
		if (!$worth) {
			return;
		}
		$count = $item->getCount();
		$player->getInventory()->setItemInHand(VanillaBlocks::AIR()->asItem());
		$session->addCoins($worth * $count);
	}

}