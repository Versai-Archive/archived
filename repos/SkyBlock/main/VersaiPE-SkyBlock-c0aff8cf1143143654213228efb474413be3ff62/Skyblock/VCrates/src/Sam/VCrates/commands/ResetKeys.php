<?php

namespace Sam\VCrates\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use Sam\VCrates\constants\Messages;
use Sam\VCrates\constants\Permissions;
use Sam\VCrates\database\Manager;

class ResetKeys extends Command{
	private Manager $database;

	public function __construct($database){
		parent::__construct("resetkeys", "Reset keys of a player", "/resetkeys <username>", []);
		$this->database = $database;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){
			if($sender->hasPermission(Permissions::RESET_KEYS)){
				$type = ["common", "rare", "epic", "legendary"];
				if(count($args) < 1) $sender->sendMessage("/resetkeys <username>");
				else if($sender->getServer()->getPlayer($args[0]) == null) $sender->sendMessage("Player not online.");
				else{
					$this->database->resetKeys($sender->getServer()->getPlayer($args[0])->getUniqueId());
					$sender->sendMessage(Messages::YES_PREFIX . Messages::PLAYER_REMOVED_KEY);
				}
			}else{
				$sender->sendMessage(Messages::NoPrefix . Messages::NO_PERMISSIONS);
			}
		}
	}
}