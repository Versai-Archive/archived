<?php

namespace Versai\vStaff\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Versai\vStaff\EventListener;

class Staff extends Command{

	public function __construct(){
		parent::__construct("staff", "Staff mode", "/staff");
		$this->setPermission("staffhud.staffmode");
	}

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			$sender->sendMessage(TF::RED . "You do not have permission to use this command.");
			return false;
		}
		if(!$sender instanceof Player){
            $sender->sendMessage(TF::RED . "You can only use this command in-game.");
		    return false;
        }
		$enabled = EventListener::isEnabled($sender) ?? false;
		if($enabled){
			$sender->sendMessage('§cDisabled Staff Mode.');
			EventListener::disableStaffMode($sender);
        }else{
			$sender->sendMessage('§aEnabled Staff Mode.');
            EventListener::enableStaffMode($sender);
        }
        return true;
    }
}