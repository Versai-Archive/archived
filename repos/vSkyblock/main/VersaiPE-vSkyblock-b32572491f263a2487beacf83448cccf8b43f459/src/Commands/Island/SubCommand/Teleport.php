<?php

declare(strict_types=1);

namespace Skyblock\Commands\Island\SubCommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\entity\Location;
use pocketmine\item\Coal;
use pocketmine\player\Player;
use Skyblock\Minions\Minion;
use Skyblock\Minions\Types\CoalMinion;
use Skyblock\Translator\Translator;
use Skyblock\Main;
use pocketmine\Server;

class Teleport extends BaseSubCommand {

	public function prepare(): void {
		$this->setPermission("skyblock.command.island");
		$this->setPermission("skyblock");
		$this->registerArgument(0, new RawStringArgument("player", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Translator::translate("commands.general.only_player"));
			return;
		}

		if (!Main::getInstance()->getSessionManager()->getSession($sender)) {
			$sender->sendMessage(Translator::translate("general.error.session_not_found"));
			return;
		}

		if (isset($args["player"])) {
			if ($args["player"] === $sender->getName()) {
				$session = Main::getInstance()->getSessionManager()->getSession($sender);
				$sender->teleport($session->getIsland()->getWorld()->getSpawnLocation());
				return;
			}
			$db = Main::getInstance()->getDatabase();
			$data = $db->getIslandByNameOffline($args["player"]);
			if (!$data) {
				$sender->sendMessage(Translator::translate("commands.island.teleport.player_not_found", [$args["player"]]));
				return;
			}
			$data = $data[0];
			if (Server::getInstance()->getWorldManager()->isWorldLoaded($data["xuid"])) {
				$sender->teleport(Server::getInstance()->getWorldManager()->getWorld($data["xuid"])->getSpawnLocation());
				return;
			}
			Server::getInstance()->getWorldManager()->loadWorld($data[0]["xuid"]);
			$island = Server::getInstance()->getWorldManager()->getWorldByName($data["xuid"]);
			$sender->teleport($island->getSpawnLocation());
			$sender->sendMessage(Translator::translate("commands.island.teleport.success_other", [$args["player"]]));
			return;
		}

		$session = Main::getInstance()->getSessionManager()->getSession($sender);
		$sender->teleport($session->getIsland()->getWorld()->getSpawnLocation());
	}
}