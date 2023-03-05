<?php

declare(strict_types=1);

namespace Skyblock\Commands\Island\SubCommand;

use CortexPE\Commando\BaseSubCommand;
use Skyblock\Island\Island;
use Skyblock\Translator\Translator;
use pocketmine\player\Player;
use Skyblock\Database\Database;
use Skyblock\Main;
use pocketmine\command\CommandSender;

class Create extends BaseSubCommand {

	public function prepare(): void {
		$this->setPermission("skyblock");
		$this->setPermission("skyblock.command.island");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Translator::translate("commands.general.only_player"));
			return;
		}

		if (Main::getInstance()->getDatabase()->playerHasIsland($sender)) {
			$sender->sendMessage(Translator::translate("commands.island.create.already_has_island"));
			return;
		}

		$session = Main::getInstance()->getSessionManager()->getSession($sender);
		$session->setIsland(new Island($sender));
		$session->getIsland()->generateIsland($session->getPlayer());
		$sender->sendMessage(Translator::translate("commands.island.create.in_progress"));
		$world = Main::getInstance()->getServer()->getWorldManager()->getWorldByName($sender->getXuid());
		if (!$world) {
			$sender->sendMessage(Translator::translate("commands.island.create.error_while_teleporting"));
			return;
		}
		$sender->teleport($world->getSpawnLocation());
		$sender->sendMessage(Translator::translate("commands.island.create.success"));
	}
}