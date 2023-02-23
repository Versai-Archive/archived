<?php


namespace Sam\VCrates\commands;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use Sam\VCrates\constants\Messages;
use Sam\VCrates\constants\Permissions;
use Sam\VCrates\Main;

class PlaceCrate extends Command{

	private Main $plugin;

	/**
	 * PlaceCrate constructor.
	 */
	public function __construct($pl){
		parent::__construct("placecrate", "Place a trapped chest who will become a crate", "/place common|rare|epic|legendary", ["place"]);
		$this->plugin = $pl;
	}


	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player && $sender->hasPermission(Permissions::PLACE_CRATE)){
			$type = ["common", "rare", "epic", "legendary"];
			$username = $sender->getName();
			if(!isset($this->plugin->placeCrate[$username])){
				if(count($args) < 1){
					$sender->sendMessage("/place common|rare|epic|legendary");
				}else if(!in_array(strtolower($args[0]), $type)){
					$sender->sendMessage("Available types: common, rare, epic, legendary");
				}else{
					$this->plugin->placeCrate[$username] = strtolower($args[0]);
					$sender->sendMessage(Messages::POSITIVE_PREFIX . Messages::PLACE_CRATE_ON);
				}
			}else{
				unset($this->plugin->placeCrate[$username]);
				$sender->sendMessage(Messages::NEGATIVE_PREFIX . Messages::PLACE_CRATE_OFF);
			}
		}else{
			$sender->sendMessage(Messages::NO_PREFIX . Messages::NO_PERMISSIONS);
		}
	}

}