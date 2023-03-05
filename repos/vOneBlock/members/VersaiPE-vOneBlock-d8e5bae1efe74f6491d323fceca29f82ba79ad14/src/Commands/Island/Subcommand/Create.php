<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Commands\Island\Subcommand;

use CortexPE\Commando\BaseSubCommand;
use czechpmdevs\multiworld\util\WorldUtils;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\WorldCreationOptions;
use Versai\OneBlock\Events\IslandCreateEvent;
use Versai\OneBlock\Main;
use Versai\OneBlock\OneBlock\OneBlock;
use Versai\OneBlock\Tasks\CreateIslandTask;
use Versai\OneBlock\Translator\Translator;

class Create extends BaseSubCommand {

	public function prepare(): void {
		$this->setPermission("oneblock;oneblock.command;oneblock.command.island");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Translator::translate("commands.player_only"));
			return;
		}

		if (Main::getInstance()->getDatabase()->playerHasIsland($sender)) {
			$sender->sendMessage(Translator::translate("commands.island.create.player_has_island"));
			return;
		}

		# Create World
		$this->generateWorld($sender);
		$world = Server::getInstance()->getWorldManager()->getWorldByName("ob-" . $sender->getXuid());
		$sender->sendMessage(Translator::translate("commands.island.create.generating_island"));
		Main::getInstance()->getScheduler()->scheduleDelayedTask(new CreateIslandTask($sender), 20);
		$session = Main::getInstance()->getSessionManager()->getSession($sender);
		$island = new OneBlock(strtolower($sender->getName()), Server::getInstance()->getWorldManager()->getWorldByName("ob-".$sender->getXuid()));
		$session->setIsland($island);
		Main::getInstance()->getIslandManager()->addIsland($island);
		(new IslandCreateEvent($island, $sender))->call();
	}

	public function generateWorld(Player $player) {
		$worldName = "ob-" . $player->getXuid();
		$generator = WorldUtils::getGeneratorByName("void");
		Server::getInstance()->getWorldManager()->generateWorld($worldName, WorldCreationOptions::create()
			->setSeed(0)
			->setGeneratorClass($generator->getGeneratorClass())
		);
		# Get World
		Server::getInstance()->getWorldManager()->loadWorld("ob-" . $player->getXuid());
	}

}