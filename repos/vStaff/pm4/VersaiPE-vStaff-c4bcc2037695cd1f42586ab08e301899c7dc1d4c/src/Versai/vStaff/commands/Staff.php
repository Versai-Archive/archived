<?php

namespace Versai\vStaff\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Versai\vStaff\Main;

class Staff extends Command{

	private Main $plugin;

	public function __construct($plugin){
		parent::__construct("staff", "Staff mode", "/staff");
		$this->plugin = $plugin;
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
		$disable = $this->plugin->vstaff->isEnabled($sender);
		if($disable){
			$sender->sendMessage('§cDisabled Staff Mode.');
			$this->plugin->vstaff->disable($sender);
			return true;
		}else{
			$sender->sendMessage('§aEnabled Staff Mode.');
			$this->plugin->vstaff->enable($sender);
			return true;
		}
	}
}