<?php

declare(strict_types=1);

namespace Skyblock\Scoreboard;

use Skyblock\Main;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use Skyblock\Translator\Translator;

class ScoreboardTask extends Task {

	public function onRun(): void {
		foreach(Server::getInstance()->getOnlinePlayers() as $player) {
			$session = Main::getInstance()->getSessionManager()->getSession($player);
			if ($session) {
				$scoreboard = new Scoreboard(Translator::translate("scoreboard.title"), "island", [$player]);
				$scoreboard->removeScoreboard();
				$scoreboard->createScoreboard();
				$scoreboard->addEntry(" ");
				$scoreboard->addEntry("§3Island§r§7: §r" . $session->getIsland()->getName());
				$scoreboard->addEntry("§3Level§r§7: §r" . $session->getIsland()->getLevel());
				ScoreboardManager::addScoreboard($scoreboard);
			}
		}
	}
}