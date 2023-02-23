<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Tasks;

use PHPUnit\Framework\MissingCoversAnnotationException;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use Versai\OneBlock\BossBar\BossBar;
use Versai\OneBlock\BossBar\DiverseBossBar;
use Versai\OneBlock\Main;
use Versai\OneBlock\OneBlock\OneBlockLevels;
use Versai\OneBlock\Scoreboard\Scoreboard;
use Versai\OneBlock\Scoreboard\ScoreboardManager;
use Versai\OneBlock\Translator\Translator;

class DisplayTask extends Task {


	public function onRun(): void {
		foreach(Server::getInstance()->getOnlinePlayers() as $player) {
			$session = Main::getInstance()->getSessionManager()->getSession($player);
			$bar = Main::getInstance()->getBossBar();
			if (!$session) {
				$bar->setColorFor([$player], BossBarColor::RED);
				$bar->setTitleFor([$player], "ERROR");
				$bar->setSubTitleFor([$player], Translator::translate("errors.session.not_found"));
				return;
			}
			if (!$session->hasIsland()) {
				$bar->setColorFor([$player], BossBarColor::RED);
				$bar->setTitleFor([$player], "ERROR");
				$bar->setSubTitleFor([$player], Translator::translate("errors.island.none"));
				return;
			}
			/** Scoreboard */

			$scoreboard = new Scoreboard(Translator::translate("scoreboard.title"), "island", [$player]);
			$scoreboard->removeScoreboard();
			$scoreboard->createScoreboard();
			$scoreboard->addEntry(" ");
			$scoreboard->addEntry("§gCoins§r§7: §r" . $session->getCoins());
			$scoreboard->addEntry("§3Blocks Broken§r§7: §r" . $session->getBlocksBroken());
			ScoreboardManager::addScoreboard($scoreboard);

			/** BossBar */

			if ($session->getIsland()->isMaxLevel()) {
				$bar->setTitleFor([$player], Translator::translate("island.level.max"));
				$bar->setColorFor([$player], BossBarColor::GREEN);
				$bar->setPercentageFor([$player], 1.0);
				$bar->setSubTitleFor([$player], Translator::translate("island.level.max_sub"));
				return;
			}
			$percentage = $session->getIsland()->getBlocksBroken() / $session->getIsland()->calculateBlocksNeededForNextLevel();
			$bar->setColorFor([$player], Utils::getBossBarColorFromPercentage($percentage))
			$bar->setPercentageFor([$player], ($session->getIsland()->getBlocksBroken() / $session->getIsland()->calculateBlocksNeededForNextLevel()));
			$bar->setSubTitleFor([$player], "§3" . $session->getIsland()->getBlocksBroken() . " §7/ §3" . $session->getIsland()->calculateBlocksNeededForNextLevel());
			$bar->setTitleFor([$player], "§eLevel " . $session->getIsland()->getLevel() . " §7: " . OneBlockLevels::getLevelName($session->getIsland()->getLevel()));
		}
	}

}