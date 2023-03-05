<?php

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Island\Subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Versai\OneBlock\Main;
use Versai\OneBlock\OneBlock\OneBlockPermissions;
use Versai\OneBlock\Translator\Translator;

class Ban extends BaseSubCommand {

	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void {
		$this->setPermission("oneblock.command");
		$this->registerArgument(0, new RawStringArgument("player"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		// Cant be null thanks to commando!
		$ban = $args["player"];

		if (!$sender instanceof Player) {
			$sender->sendMessage(Translator::translate("commands.player_only"));
			return;
		}

		$islandWorld = str_starts_with($sender->getWorld()->getFolderName(), "ob-") ? $sender->getWorld() : null;

		if (!$islandWorld) {
			$sender->sendMessage(Translator::translate("island.errors.not_island"));
			return;
		}

		$ownerXuid = str_replace("ob-", "", $islandWorld->getFolderName());

//		$members = json_decode(Main::getInstance()->getDatabase()->getIslandMembers($ownerXuid)["members"]);

		$memberData = Main::getInstance()->getDatabase()->getPlayerDataFromUsername($ban);

		if (!$memberData) {
			$sender->sendMessage(Translator::translate("errors.general", ["NT_IN_DB"]));
			return;
		}

		$owner = $sender->getXuid() == $ownerXuid;

		$memberData = $memberData[0];

		var_dump($memberData);

		// TODO: Right now it is just creating an array thats not used, we need to implement the manager
		// so that we can edit the island object

		$island = Main::getInstance()->getIslandManager()->getIslandByXuid($ownerXuid);

		if (!$island) {
			var_dump(Main::getInstance()->getIslandManager()->getIslands());
			$sender->sendMessage(Translator::translate("errors.general", ["ISLND_NOT_MNGD"]));
			return;
		}

		$members = $island->getMembers();

		# If player not the island owner
		if (!$owner) {
			# If the player is set into the members
			if (isset($members[$sender->getXuid()])) {
				# If player has permission
				if (in_array(OneBlockPermissions::BAN_PLAYERS, $members[$sender->getXuid()])) {
					# If the player trying to ban is already a member
					if (isset($members[$memberData["xuid"]])) {
						$members[$memberData["xuid"]] += [OneBlockPermissions::BANNED];
						$island->setMembers($members);
						Main::getInstance()->getDatabase()->updateOfflineIsland($island);
						$sender->sendMessage(Translator::translate("commands.island.ban.success", [$ban]));
						return;
					}
					$members[] = [$memberData["xuid"] => [OneBlockPermissions::BANNED]];
					$island->setMembers($members);
					$sender->sendMessage(Translator::translate("commands.island.ban.success", [$ban]));
					return;
				}
				$sender->sendMessage(Translator::translate("commands.island.ban.bad_perms"));
				return;
			}
			$sender->sendMessage(Translator::translate("island.not_member"));
			return;
		}

		if (isset($members[$memberData["xuid"]])) {
			$members[$memberData["xuid"]] += [OneBlockPermissions::BANNED];
			$island->setMembers($members);
			Main::getInstance()->getDatabase()->updateOfflineIsland($island);
			$sender->sendMessage(Translator::translate("commands.island.ban.success", [$ban]));
		}
		$members += [$memberData["xuid"] => [OneBlockPermissions::BANNED]];
		$island->setMembers($members);
		Main::getInstance()->getDatabase()->updateOfflineIsland($island);
		$sender->sendMessage(Translator::translate("commands.island.ban.success", [$ban]));
	}
}