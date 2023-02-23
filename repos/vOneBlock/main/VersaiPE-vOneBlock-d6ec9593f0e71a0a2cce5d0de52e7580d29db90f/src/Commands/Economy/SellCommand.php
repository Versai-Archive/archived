<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Economy;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use Versai\OneBlock\Economy\EconomyManager;
use Versai\OneBlock\Forms\ModalForm;
use Versai\OneBlock\Main;
use Versai\OneBlock\Translator\Translator;

class SellCommand extends BaseCommand {


	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void {
		$this->setPermission("oneblock");
		$this->setPermission("oneblock.command");
		$this->setDescription("Sell your items");
		$this->registerArgument(0, new RawStringArgument("type", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Translator::translate("commands.player_only"));
			return;
		}

		$session = Main::getInstance()->getSessionManager()->getSession($sender);

		if (!$session) {
			$sender->sendMessage(Translator::translate("errors.session.not_found"));
		}

		if (isset($args["type"])) {
			switch ($args["type"]) {
				case "all":
					$form = new ModalForm(function (Player $player, $clicked) {
						if (!$clicked && $clicked != 0) {
							return;
						}
						if ($clicked == 0) {
							EconomyManager::sellItemsInInventory($player);
							return;
						}
					});
					$form->setTitle("Sell Confirmation");
					$items = EconomyManager::getSellableItems($sender);
					$content = [];
					foreach ($items as $item) {
						if (isset($content[$item->getVanillaName()])) {
							$content[$item->getVanillaName()] += $item->getCount();
							continue;
						}
						$content[$item->getVanillaName()] = $item->getCount();
					}

					$formatted = "§7";

					foreach($content as $item => $count) {
						$price = EconomyManager::getItemPrice(StringToItemParser::getInstance()->parse($item));
						$total = $price * $count;
						$formatted .= $item . " §7x§3" . $count . " §a\$§7" . $total . "\n";
					}

					$form->setContent($formatted);
					$form->setButton1("§cNo");
					$form->setButton2("§aSell");
					$sender->sendForm($form);
					break;
				default:
					EconomyManager::sellItemInHand($sender);
					break;
			};
		} else {
			EconomyManager::sellItemInHand($sender);
		}
	}
}