<?php

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Island\Subcommand;

use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\OneBlock\Forms\CustomForm;
use Versai\OneBlock\Forms\SimpleForm;
use Versai\OneBlock\Main;
use Versai\OneBlock\OneBlock\OneBlock;
use Versai\OneBlock\OneBlock\OneBlockPermissions;
use Versai\OneBlock\Translator\Translator;
use function PHPUnit\Framework\assertFalse;

class AddMember extends BaseSubCommand {


	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void {
		$this->registerArgument(0, new TextArgument("player"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$isIsland = str_starts_with($sender->getWorld()->getFolderName(), "ob-");

		if (!$isIsland) {
			$sender->sendMessage(Translator::translate("island.errors.not_island"));
			return;
		}

		$island = Main::getInstance()->getIslandManager()->getIslandByXuid(str_replace("ob-", "", $sender->getWorld()->getFolderName()));

		if (!$island) {
			$sender->sendMessage(Translator::translate("errors.general", ["ISLND_NOT_MNGD"]));
			return;
		}

		$isOwner = $sender->getXuid() == $island->getOwner();

		$target = Main::getInstance()->getDatabase()->getPlayerDataFromUsername($args["player"]);

		if (!$target) {
			$sender->sendMessage(Translator::translate("commands.player_not_found"));
			return;
		}

		if ($isOwner) {
			$this->Rank($sender, $target[0], $island);
			return;
		}

		if (isset($island->getMembers()[$sender->getXuid()])) {
			if (in_array(OneBlockPermissions::ADD_MEMBER, $island->getMembers()[$sender->getXuid()])) {
				$this->Rank($sender, $target, $island);
				return;
			}
		}
		$sender->sendMessage(Translator::translate("commands.island.add_member.bad_perms"));
	}

	public function Rank(Player $player, array $target, OneBlock $island) {
		$form = new SimpleForm(function(Player $player, $clicked) use ($island, $target) {
			// X clicked
			if (!$clicked && $clicked != 0) {
				return;
			}

			$session = Main::getInstance()->getSessionManager()->getSession($player);

			if (!$session) {
				$player->sendMessage(Translator::translate("errors.session.none"));
				return;
			}

			if (!$session->hasIsland()) {
				$player->sendMessage(Translator::translate("errors.island.none"));
				return;
			}

			$island = Main::getInstance()->getIslandManager()->getIslandByXuid($player->getXuid());

			if (!$island) {
				$player->sendMessage(Translator::translate("errors.general", ["ISLND_NOT_MNGD"]));
				return;
			}

			$data = yaml_parse_file(Main::getInstance()->getDataFolder() . "permissions.yml");

			if (isset($island->getMembers()[$target["xuid"]])) {
				$player->sendMessage("§cPlayer is already a member of your island or is banned use §e/is manage §cto edit members");
				return;
			}

			if (!$data) {
				return;
			}

			$permissions = [];

			switch ($clicked) {
				case 0: // Member
					if (!isset($data["member"])) {
						Main::getInstance()->getLogger()->warning("Member for base permissions member does not exist in permissions.yml");
						return;
					}

					foreach ($data["member"] as $permission) {
						$permissions[] = $permission;
					}
					$island->addMember($target["xuid"], $permissions);
					break;

				case 1: // Admin
					if (!isset($data["admin"])) {
						Main::getInstance()->getLogger()->warning("Member for base permissions member does not exist in permissions.yml");
						return;
					}

					foreach ($data["admin"] as $permission) {
						$permissions[] = $permission;
					}
					$island->addMember($target["xuid"], $permissions);
					break;

				case 2: // Co owner
					if (!isset($data["co-owner"])) {
						Main::getInstance()->getLogger()->warning("Member for base permissions member does not exist in permissions.yml");
						return;
					}

					foreach ($data["co-owner"] as $permission) {
						$permissions[] = $permission;
					}
					$island->addMember($target["xuid"], $permissions);
					break;

				case 3: // Custom
					$this->Permissions($player);
					break;
			}
		});

		$form->addButton("§7Member"); // 0
		$form->addButton("§6Admin"); // 1
		$form->addButton("§3Co§7-§3Owner"); // 2
		$form->addButton(TextFormat::colorize("&4C&cU&6S&eT&aO&2M")); // 3
		$player->sendForm($form);
	}

	public function Permissions(Player $player) {
		$form = new CustomForm(function(Player $player, $data) {
			var_dump($data);
		});

		$allPerms = (new \ReflectionClass(OneBlockPermissions::class))->getConstants();

		foreach($allPerms as $perm) {
			if ($perm == OneBlockPermissions::BANNED) {
				continue;
			}
			$form->addToggle($perm);
		}

		$player->sendForm($form);
	}
}