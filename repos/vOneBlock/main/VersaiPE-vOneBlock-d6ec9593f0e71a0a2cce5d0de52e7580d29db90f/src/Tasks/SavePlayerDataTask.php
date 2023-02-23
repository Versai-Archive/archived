<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use Versai\OneBlock\Main;

class SavePlayerDataTask extends Task {

	public function onRun(): void {
		foreach(Server::getInstance()->getOnlinePlayers() as $player) {
			$session = Main::getInstance()->getSessionManager()->getSession($player);
			if (!$session) {
				return;
			}
			Main::getInstance()->getDatabase()->updatePlayer($session);
			Main::getInstance()->getDatabase()->updatePlayerIsland($session);
		}
	}

}