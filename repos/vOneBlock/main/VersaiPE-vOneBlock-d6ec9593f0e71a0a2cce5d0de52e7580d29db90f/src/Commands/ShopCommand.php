<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use Versai\OneBlock\Forms\CustomForm;
use Versai\OneBlock\Forms\SimpleForm;
use Versai\OneBlock\Main;
use Versai\OneBlock\Translator\Translator;

class ShopCommand extends BaseCommand {

	protected function prepare(): void {
		$this->setDescription("Shop for items");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Translator::translate("commands.player_only"));
			return;
		}

		$shop = yaml_parse_file(Main::getInstance()->getDataFolder() . "shop.yml");

		$data = $shop["shop"];
		$this->Category($sender, "Shop", $data);
	}

	public function Category(Player $player, string $name, array $data) {
		$form = new SimpleForm(function(Player $player, int $res = null) use ($data): void {
			if (!$res && $res != 0) {
				return;
			}

			if(isset($data[$res])) {

				if(isset($data[$res]["sub"])) {
					$this->Category($player, $data[$res]["name"], $data[$res]["sub"]);
					return;
				}

				if (isset($data[$res]["items"])) {
					$this->Items($player, $data[$res]["name"], $data[$res]["items"]);
				}
			}
		});
		$form->setTitle($name);
		foreach($data as $datum => $category) {
			$form->addButton($category["name"], SimpleForm::IMAGE_TYPE_PATH, $category["texture"]);
		}
		$player->sendForm($form);
	}

	public function Items(Player $player, string $category, array $items) {
		$form = new SimpleForm(function(Player $player, int $res = null) use ($items): void {
			if (!$res && $res != 0) {
				return;
			}

			if(isset($items[$res])) {
				$this->Item($player, $items[$res]["name"], $items[$res]);
			}
		});
		foreach($items as $id => $item) {
			$form->addButton($item["name"] . "\n§7\$§e" . $item["cost"], SimpleForm::IMAGE_TYPE_PATH, $item["texture"]);
		}
		$player->sendForm($form);
	}

	public function Item(Player $player, string $item, array $itemData) {
		$itemMeta = $itemData["item"];
		$id = $itemMeta["id"];
		$meta = 0;
		if (isset($itemMeta["meta"])) {
			$meta = $itemMeta["meta"];
		}
		$realItem = ItemFactory::getInstance()->get($id, $meta);
		$maximum = $player->getInventory()->getAddableItemQuantity($realItem);
		$form = new CustomForm(function(Player $player, $data) use ($itemData, $realItem): void {
			if (!$data) {
				return;
			}
			$session = Main::getInstance()->getSessionManager()->getSession($player);
			if (!$session) {
				$player->sendMessage(Translator::translate("errors.session.not_found"));
				return;
			}
			if($session->hasSufficentFunds((int)ceil($itemData["cost"] * $data[0]))) {
				$player->getInventory()->addItem($realItem);
				$session->removeCoins((int)ceil($itemData["cost"] * $data[0]));
			}
		});
		$form->setTitle($item);
		$form->addSlider(Translator::translate("ui.shop.amount"), 1, $maximum, 1);
		$player->sendForm($form);
	}
}
