<?php

declare(strict_types = 1);

/**
 * This file is for recovering mana every x time
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Tasks;

use Versai\RPGCore\Main;

use pocketmine\scheduler\Task;

class ManaRecoverTask extends Task {

    /** @var Main **/
    private Main $plugin;
	
	/**
	* ManaRecoverTask Constructor.
	*
	* @param Main $plugin
	**/
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
    }
	
    public function onRun() : void {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $session = $this->plugin->getSessionManager();
            $playerSession = $session->getSession($player);

            if (!$playerSession) {
                return;
            }

            if($playerSession->getMana() < $playerSession->getMaxMana()) {
                $mana = $playerSession->getMana();

                $playerSession->setMana($mana + 1);
            }
        }
    }
}