<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Island;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use Versai\OneBlock\Commands\Island\Subcommand\AddMember;
use Versai\OneBlock\Commands\Island\Subcommand\Ban;
use Versai\OneBlock\Commands\Island\Subcommand\Create;
use Versai\OneBlock\Commands\Island\Subcommand\Delete;
use Versai\OneBlock\Commands\Island\Subcommand\Teleport;
use Versai\OneBlock\Translator\Translator;

class IslandCommand extends BaseCommand {
    public function prepare(): void {
		$this->registerSubCommand(new Create("create", "create a island"));
		$this->registerSubCommand(new Teleport("tp", "teleport to your's or someone elses island", ["teleport, go"]));
		$this->registerSubCommand(new Ban("ban", "Ban a player from an island"));
		$this->registerSubCommand(new AddMember("addmember", "add a member to your island", ["addmem", "addhelper", "addhelp", "am"]));
		$this->registerSubCommand(new Delete("delete", "delete your island!"));
		$this->setDescription(Translator::translate("commands.island.description"));
		$this->setPermissionMessage("You dont have permission to do that!");
		$this->setPermission("oneblock;oneblock.command;oneblock.command.island");
		$this->setAliases(["is", "oneblock", "ob"]);
    }

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$this->sendUsage();
	}
}
