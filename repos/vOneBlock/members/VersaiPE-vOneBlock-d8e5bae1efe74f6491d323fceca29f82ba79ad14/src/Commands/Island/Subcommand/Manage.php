<?php
/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Island\Subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\OneBlock\Forms\CustomForm;
use Versai\OneBlock\Forms\SimpleForm;
use Versai\OneBlock\Main;
use Versai\OneBlock\OneBlock\OneBlockPermissions;
use Versai\OneBlock\OneBlock\OneBlockType;
use Versai\OneBlock\Translator\Translator;

class Manage extends BaseSubCommand {

	protected function prepare(): void {
		$this->setPermission("oneblock;oneblock.commands;oneblock.commands.island");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {

		if (!$sender instanceof Player) {
			$sender->sendMessage(Translator::translate("commands.player_only"));
			return;
		}

		// If the world the player is at is a island

		$island = Main::getInstance()->getIslandManager()->getIsland($sender->getWorld());

		if (!$island) {
			$sender->sendMessage(Translator::translate("islands.errors.not_island"));
			return;
		}

		// Permission check

		$isOwner = $island->getOwner() === $sender->getXuid();

		if(isset($island->getMembers()[$sender->getXuid()]) || $isOwner) {
			$mainForm = new SimpleForm(function (Player $player, $data) use ($island) {
				if (!$data && $data != 0) { // "X" clicked
					return;
				}
				switch($data) {

					case 0: // members
						$membersForm = new SimpleForm(function(Player $player, $data) use ($island) {
							if (!$data) { // X Clicked
								return;
							}

							$member = $island->getMemberPermissions($data);

							if (!$member) {
								$player->sendMessage(TextFormat::RED . "Cant find this player!");
								return;
							}

							$playerForm = new CustomForm(function(Player $player, $dat) use ($island, $data) {
								if (!$dat) { // X clicked
									return;
								}

								$perms = [];

								foreach($dat as $perm => $value) {
									if($value) {
										$perms[] = $perm;
									}
								}
								var_dump($perms);
								$island->setMemberPermissions($data, $perms);
							});

							$allPerms = (new \ReflectionClass(OneBlockPermissions::class))->getConstants();

							foreach($allPerms as $perm) {
								if ($perm == OneBlockPermissions::BANNED || $perm == OneBlockPermissions::FLY) {
									continue;
								}
								$playerForm->addToggle(Translator::translate($perm), in_array($perm, $member), $perm);
							}

							$player->sendForm($playerForm);

						});

						$members = $island->getMembers();

						foreach($members as $member => $perms) {
							$playerInfo = Main::getInstance()->getDatabase()->getPlayerData((string)$member);

							if (!$playerInfo) {
								continue;
							}

							$membersForm->addButton($playerInfo[0]["username"] . TextFormat::GRAY . " - " . $island->getRankFromPerms($perms), SimpleForm::IMAGE_TYPE_URL, "http://localhost:3000/user/" . $playerInfo[0]["username"], $playerInfo[0]["xuid"]);
						}
						$membersForm->setTitle("Island Members");
						$player->sendForm($membersForm);
					break;

					case 1: // Type
						$form = new SimpleForm(function (Player $player, $data) {
							$island = Main::getInstance()->getIslandManager()->getIsland($player->getWorld());

							if (!$island) {
								$player->sendMessage(Translator::translate("errors.general", ["ISLND_NOT_MNGD"]));
								return;
							}

							$island->setType($data);
							$player->sendMessage("Island type is now set to " . TextFormat::RED .  $data);
						});

						$types = (new \ReflectionClass(OneBlockType::class))->getConstants();

						foreach	($types as $type) {
							$form->addButtonNoImage(Translator::translate($type), $type);
						}
						$form->setTitle("Set Island Type");
						$form->setContent("This will update the type of island your island is");
						$player->sendForm($form);
					break;

					case 2:
						$player->sendMessage(TextFormat::RED . "This feature is still in development!");
					break;

					case 3:
						/*$visibility = new CustomForm(function(Player $player, $data) {
							if (!$data) { // X is clicked
								return;
							}

							$island = Main::getInstance()->getIslandManager()->getIslandByXuid(str_replace("ob-", "", $player->getWorld()->getFolderName()));

							if (!$island) {
								$player->sendMessage(Translator::translate("errors.general", ["ISLND_NOT_MNGD"]));
								return;
							}

							$island->setLocked($data[0]);
						});

						$visibility->setTitle("Change Visibility");
						$visibility->addToggle("Lock Island?");
						$player->sendForm($visibility);*/
						// TODO
						$player->sendMessage(TextFormat::RED . "This feature is still in development!");
					break;
				}
			});

			$buttons = [
				"§7Members", // 0
				"§5Type", // 1
				"§9Island", // 2
				"§3Visibility", // 3
			];

			foreach($buttons as $button) {
				$mainForm->addButton($button);
			}

			$mainForm->setTitle("§7Manage island : §a{$island->getOwner()}");
			$sender->sendForm($mainForm);
		} else {
			$sender->sendMessage(Translator::translate("island.permissions.not_member"));
			return;
		}
	}
}
