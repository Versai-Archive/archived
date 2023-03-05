<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Island\Subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use Versai\OneBlock\Main;
use Versai\OneBlock\OneBlock\OneBlock;
use Versai\OneBlock\OneBlock\OneBlockPermissions;
use Versai\OneBlock\Translator\Translator;

class Teleport extends BaseSubCommand {


	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void {
		$this->setPermission("oneblock;oneblock.command;oneblock.command.island");
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

		# If the player is wanting to go to another players island
		if (isset($args["player"])) {
			# If the player argument is the sender
			if (strtolower($args["player"]) === strtolower($sender->getName())) {
				$session = Main::getInstance()->getSessionManager()->getSession($sender);
				$sender->teleport($session->getIsland()->getWorld()->getSpawnLocation());
				return;
			}
			$db = Main::getInstance()->getDatabase();
			$_player = Server::getInstance()->getPlayerByPrefix($args["player"]);
			$name = ($_player) ? $_player->getName() : $args["player"];
			$data = $db->getIslandByUsername(strtolower($name));
			if (!$data) {
				$sender->sendMessage(Translator::translate("commands.island.teleport.player_not_found", [$args["player"]]));
				return;
			}
			$data = $data[0];
			$members = json_decode($db->getIslandMembers($name)["members"], true);
			# If player was not in database, or has no island
			# Check if the player is banned in that island
			if(isset($members[$sender->getXuid()])) {
				$banned = in_array(OneBlockPermissions::BANNED, $members[$sender->getXuid()]);
				if ($banned) {
					$sender->sendMessage(Translator::translate("commands.island.teleport.banned"));
					return;
				}
			}
			# If the player has a world, that is loaded
			if (!Main::getInstance()->getIslandManager()->islandIsRegistered($data["owner_xuid"])) {
				$worldLoaded = Server::getInstance()->getWorldManager()->isWorldLoaded("ob-" . $data["owner_xuid"]);
				if (!$worldLoaded) {
					Server::getInstance()->getWorldManager()->loadWorld("ob-" . $data["owner_xuid"]);
				}
				$world = Server::getInstance()->getWorldManager()->getWorldByName("ob-" . $data["owner_xuid"]);
				Main::getInstance()->getIslandManager()->addIsland(new OneBlock($data["owner_xuid"], $world));
			}

			if (Main::getInstance()->getIslandManager()->getIslandByXuid($data["owner_xuid"])->getWorld()->isLoaded()) {
				$sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName("ob-".$data["owner_xuid"])->getSpawnLocation());
				return;
			}
			# Load the world that the player is trying to go to
			Server::getInstance()->getWorldManager()->loadWorld("ob-" . $data["owner_xuid"]);
			$island = Server::getInstance()->getWorldManager()->getWorldByName("ob-" . $data["owner_xuid"]);
			# Teleport them to it
			$sender->teleport($island->getSpawnLocation());
			$sender->sendMessage(Translator::translate("commands.island.teleport.success_other", [$name]));
			return;
		}

		$session = Main::getInstance()->getSessionManager()->getSession($sender);
		if ($session->hasIsland()) {
			$sender->teleport($session->getIsland()->getWorld()->getSpawnLocation());
			return;
		}
		$worldLoaded = Server::getInstance()->getWorldManager()->loadWorld("ob-".$sender->getXuid());
		if (!$worldLoaded) {
			$sender->sendMessage(Translator::translate("commands.island.teleport.error", ["ERR_WORLD_LOAD_FAILURE"]));
			return;
		}
		$island = Server::getInstance()->getWorldManager()->getWorldByName("ob-".$sender->getXuid());
		$sender->teleport($island->getSpawnLocation());
	}
}