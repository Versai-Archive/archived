<?php

declare(strict_types=1);

namespace Skyblock\Commands\Basic;

use muqsit\invmenu\InvMenu;
use Skyblock\Main;
use Skyblock\Translator\Translator;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use CortexPE\Commando\BaseCommand;

class CraftCommand extends BaseCommand {

	public function prepare(): void {
		//	$this->setPermission("command.craft");
		$this->setPermissionMessage(Translator::translate("commands.general.no_permission"));
		$this->setDescription(Translator::translate("commands.craft.description"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if(!$sender instanceof Player){
			$sender->sendMessage(Translator::translate("commands.general.only_player"));
			return;
		}
		InvMenu::create("portable:crafting")->send($sender);
		return;
	}
}