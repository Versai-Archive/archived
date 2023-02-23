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
use Versai\RPGCore\Utils\ScoreboardManager;

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
            $player->sendTip(
				"§c ".round($player->getHealth())."/".$player->getMaxHealth().
				"      §7".round($player->getPosition()->getX()).
				" §f".Compass::getCompassEmoji($player->getLocation()->getYaw() ?? .0).
				" §7".round($player->getPosition()->getZ()).
				"      §b ".$player->getMana()."/20"
			);

			//Scoreboard stuff


			# SCOREBOARD DISPLAY
			
        } // ۞
    }
}