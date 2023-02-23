<?php

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Island\Subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use czechpmdevs\multiworld\util\WorldUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use Versai\OneBlock\Main;
use Versai\OneBlock\Translator\Translator;

class Delete extends BaseSubCommand {

	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void {
		$this->setPermission("oneblock;oneblock.command;oneblock.command.island");
		$this->registerArgument(0, new RawStringArgument("confirm", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Translator::translate("commands.player_only"));
			return;
		}

		$session = Main::getInstance()->getSessionManager()->getSession($sender);

		if (!$session) {
			$sender->sendMessage(Translator::translate("errors.session.not_found"));
			return;
		}

		if (!$session->getIsland()) {
			$sender->sendMessage(Translator::translate("errors.island.none"));
			return;
		}

		if (!Main::getInstance()->getDatabase()->getIslandByXuid($sender->getXuid())) {
			$sender->sendMessage("Your island is not in the database!");
			return;
		}

		if (isset($args["confirm"])) {
			if ($args["confirm"] != "confirm") {
				$sender->sendMessage("§7To delete your island please run §e/is delete confirm");
				return;
			}
			Main::getInstance()->getDatabase()->deleteIsland($sender->getXuid());
			$loaded = Server::getInstance()->getWorldManager()->isWorldLoaded("ob-" . $sender->getXuid());
			if ($loaded) {
				Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName("ob-" . $sender->getXuid()));
			}
			WorldUtils::removeWorld("ob-".$sender->getXuid());
			$session->setIsland(null);
			$sender->sendMessage(Translator::translate("commands.island.delete.complete"));
			return;
		}
		$sender->sendMessage("§7To delete your island please run §e/is delete confirm");
	}
}