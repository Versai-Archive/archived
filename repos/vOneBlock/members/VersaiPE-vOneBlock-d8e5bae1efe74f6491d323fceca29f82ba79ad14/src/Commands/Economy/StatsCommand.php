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
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use Versai\OneBlock\Forms\CustomForm;
use Versai\OneBlock\Main;

class StatsCommand extends BaseCommand {


	protected function prepare(): void
	{
		$this->setDescription("Get stats of different economy things");
		$this->setUsage("/estats (sell)");
		$this->registerArgument(0, new RawStringArgument("stat"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) return;
		if (isset($args["stat"])) {
			if ($args["stat"] == "sell") {
				$form = new CustomForm(function () {
				});
				$items = [];
				foreach (Main::getInstance()->getConfig()->get("sell") as $item => $price) {
					$items[] = "§7" . $item . " = §a\${$price}";
				}
				$form->addLabel(join("\n", $items));
				$sender->sendForm($form);
			}
		} else {
			$this->sendUsage();
			return;
		}
	}
}