<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Developer;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use Versai\OneBlock\Forms\CustomForm;
use Versai\OneBlock\Main;
use Versai\OneBlock\Translator\Translator;

class DumpCommand extends BaseCommand {


	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void {
		$this->registerArgument(0, new RawStringArgument("player"));
		$this->registerArgument(1, new RawStringArgument("dumping"));
		$this->setPermission("oneblock.developer");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!isset($args["dumping"])) {
			$sender->sendMessage(Translator::translate("command.error.no_args"));
			var_dump(0);
			return;
		}

		// IK that match is a thing, but switch case seems better in this case
		switch($args["dumping"]) {
			case "session":
			case "ses":
				if (!isset($args[1])) {
					$this->sendUsage();
					var_dump(1);
				}
				$player = Server::getInstance()->getPlayerByPrefix($args["player"]);
				if (!$player) {
					$sender->sendMessage(Translator::translate("commands.player_not_found"));
					return;
				}
				$session = Main::getInstance()->getSessionManager()->getSession($player);
				$form = new CustomForm(null);
				$form->addLabel(
					"Kills: " . $session->getKills() . "\n" .
					"Deaths: " . $session->getDeaths() . "\n" .
					"Blocks Broken: " . $session->getBlocksBroken() . "\n"
				);
				$form->setTitle("Session Information: {$player->getName()}");
				if ($sender instanceof Player) {
					$sender->sendForm($form);
				}
				$sender->sendMessage(
					"Kills: " . $session->getKills() . "\n" .
					"Deaths: " . $session->getDeaths() . "\n" .
					"Blocks Broken: " . $session->getBlocksBroken() . "\n"
				);
		}
	}
}