<?php

namespace Sam\VCrates\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Sam\VCrates\constants\Rarity;
use Sam\VCrates\database\Manager;

class Keys extends Command{
	private Manager $database;

	public function __construct($database){
		parent::__construct("keys", "See your keys", "/keys", ["key", "k"]);
		$this->database = $database;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){
			$this->database->getPlayerID($sender->getUniqueId(), function($id) use ($sender){
				$this->database->getPlayerKeys($id, function($keys) use ($sender){
					$sender->sendMessage(
						TF::BLACK . "----------\n" .
						Rarity::COMMON . "Keys : $keys[0]\n" .
						Rarity::RARE . "Keys : $keys[1]\n" .
						Rarity::EPIC . "Keys : $keys[2]\n" .
						Rarity::LEGENDARY . "Keys : $keys[3]\n" .
						TF::BLACK . "----------\n"
					);
				});
			});
		}
	}
}