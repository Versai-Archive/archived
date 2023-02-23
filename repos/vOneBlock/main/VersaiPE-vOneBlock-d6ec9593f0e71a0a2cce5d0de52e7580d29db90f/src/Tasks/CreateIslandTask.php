<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Tasks;

use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\Position;
use Versai\OneBlock\Translator\Translator;

class CreateIslandTask extends Task {

	public function __construct(Player $player) {
		$this->player = $player;
	}

	public function onRun(): void {
		$world = Server::getInstance()->getWorldManager()->getWorldByName("ob-" . $this->player->getXuid());
//		$world->setBlock(new Vector3(256, 64, 256), VanillaBlocks::GRASS());
		$world->setSpawnLocation(new Position(256.5, 66, 256.5, $world));
		$this->player->teleport($world->getSpawnLocation());
		$this->player->sendMessage(Translator::translate("commands.island.create.successful"));
	}

}