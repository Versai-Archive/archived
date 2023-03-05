<?php

declare(strict_types=1);

namespace Skyblock\Commands\Island;

use CortexPE\Commando\BaseCommand;
use Skyblock\Commands\Island\SubCommand\Create;
use Skyblock\Commands\Island\SubCommand\Teleport;
use Skyblock\Translator\Translator;
use pocketmine\command\CommandSender;

class IslandCommand extends BaseCommand {

	public function prepare(): void {
		$this->registerSubCommand(new Teleport("teleport", "Teleport to your island or someone elses", ["tp"]));
		$this->registerSubCommand(new Create("create", "Create an island"));
		$this->setDescription(Translator::translate("commands.island.description"));
		$this->setPermissionMessage(Translator::translate("commands.general.no_permission"));
		$this->setPermission("skyblock.command.island");
		$this->setPermission("skyblock");
		$this->setAliases(["is", "i"]);
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$this->sendUsage();
	}

}