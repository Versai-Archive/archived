<?php

namespace Sam\VCrates\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use Sam\VCrates\constants\Messages;
use Sam\VCrates\constants\Permissions;
use Sam\VCrates\database\Manager;

class RemoveKeys extends Command{
	private Manager $database;

	public function __construct($database){
		parent::__construct("removekeys", "Remove keys", "/rk <username> <type> <amount>", ["rk"]);
		$this->database = $database;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){
			if($sender->hasPermission(Permissions::REMOVE_KEYS)){
				$type = ["common", "rare", "epic", "legendary"];
				if(count($args) < 3) $sender->sendMessage("/rk <username> <type> <amount>");
				else if($sender->getServer()->getPlayer($args[0]) == null) $sender->sendMessage("Player not online.");
				else if(!in_array(strtolower($args[1]), $type)) $sender->sendMessage("Available types: common, rare, epic, legendary");
				else if(!ctype_digit($args[2])) $sender->sendMessage("Check your amount again");
				else{
					$this->database->removeKeys($sender->getServer()->getPlayer($args[0])->getUniqueId(), (int) $args[2], $args[1]);
					$sender->sendMessage(Messages::YES_PREFIX . Messages::PLAYER_REMOVED_KEY);
				}
			}else{
				$sender->sendMessage(Messages::NoPrefix . Messages::NO_PERMISSIONS);
			}
		}
	}
}