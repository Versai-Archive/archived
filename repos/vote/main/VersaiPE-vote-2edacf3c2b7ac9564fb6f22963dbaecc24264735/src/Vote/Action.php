<?php

namespace Vote;

use ARTulloss\Groups\Groups;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

/**
 * @property Vote plugin
 */
class Action{

	private $groups;

	/**
	 * Action constructor.
	 * @param Vote $plugin
	 */
    public function __construct(Vote $plugin){
        $this->plugin=$plugin;
    }

    /**
     * This function is for developers,
     * He can performs many operations
     * on the players and other environments.
     *
     * @param Player $player
     */
    public function Player(Player $player){
    	if($this->groups === null)
    		$this->groups = Groups::getInstance();
    	$name = $player->getName();
    	$data = $this->groups->playerHandler->getPlayerData($name);
    	if($data->getGroup() === $this->groups->getDefaultGroupName())
    		$player->getServer()->dispatchCommand(new ConsoleCommandSender(), "group set \"$name\" Voter 1 day");
    }
}





