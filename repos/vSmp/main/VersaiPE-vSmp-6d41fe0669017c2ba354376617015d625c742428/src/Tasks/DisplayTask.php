<?php

declare(strict_types = 1);

/**
 * This file is for displaying player info in an actionbar
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Tasks;

use Versai\RPGCore\Main;
use Versai\RPGCore\Utils\Compass;

use pocketmine\scheduler\Task;

use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
// use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
// use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\player\Player;
use Versai\RPGCore\Utils\Emoji;
use Versai\RPGCore\Utils\ScoreboardManager;
use xenialdan\apibossbar\BossBar;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\utils\TextFormat as TF;

class DisplayTask extends Task {

    /** @var Main **/
    private Main $plugin;
	
	/**
	* DisplayTask Constructor.
	*
	* @param Main $plugin
	**/
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
    }
	
    public function onRun() : void {
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			$manager = $this->plugin->getSessionManager();
			$playerSession = $manager->getSession($player);

            $player->setNameTag($player->getDisplayName() . PHP_EOL . TF::RED . $player->getHealth() . "§7/" . $player->getMaxHealth() . "§r");

			if(!$playerSession) {
				$player->sendTip("§cAn error occured there is no session currentlly");
				return;
			}

            $player->sendActionBarMessage(
				"§c " . Emoji::HEART . " " . round($player->getHealth())."§7/§c".$player->getMaxHealth(). "\n\n" .
				//" §7".round($player->getPosition()->getX()).
				//" §f".Compass::getCompassEmoji($player->getLocation()->getYaw() ?? .0).
				//" §7".round($player->getPosition()->getZ()). "\n" .
				"§b " . Emoji::BLUE_FLAME . " " . $playerSession->getMana()."§7/§b".$playerSession->getMaxMana() . "\n\n" .
				"§7 " . Emoji::SHEILD . " " . $playerSession->getDefense() . "\n\n" .
				"§g " . Emoji::COIN . " " . $playerSession->getCoins()
			);

			$player->sendTip(
				" §7".round($player->getPosition()->getX()).
				" §f".Compass::getCompassEmoji($player->getLocation()->getYaw() ?? .0).
				" §7".round($player->getPosition()->getZ())
			);

			if(!$playerSession->getQuest()) {
				return;
			}

			$bar = Main::getInstance()->getBossBar();
            $bar->setTitleFor([$player], $playerSession->getQuest()->getVisual());
            $bar->setSubTitleFor([$player], $playerSession->getQuest()->getDescription());
            $bar->setPercentageFor([$player], $playerSession->getQuestProgress() / $playerSession->getQuestRequired());
			$bar->setColor($playerSession->getQuest()->getBarColor());//$playerSession->getQuest()->getBarColor());
			//Scoreboard stuff
			$scoreboard = new ScoreboardManager($this->plugin);
			$scoreboard->update();

			# SCOREBOARD DISPLAY
        } // ۞
    }
}