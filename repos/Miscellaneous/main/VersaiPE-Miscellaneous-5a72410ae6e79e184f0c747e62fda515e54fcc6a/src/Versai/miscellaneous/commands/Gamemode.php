<?php
declare(strict_types=1);

namespace Versai\miscellaneous\commands;

use Versai\miscellaneous\Constants;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use pocketmine\player\GameMode as PMGameMode;

class Gamemode extends Command{

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player) {
            return;
        }
		
		if(isset($args[0])) {
			switch ($args[0]) {
				case 'c':
				case 'creative':
					if($sender->hasPermission(Constants::CREATIVE))
						$sender->setGamemode(PMGameMode::CREATIVE());
					else {
						$sender->sendMessage(Constants::NO_PERMISSION);
						return;
					}
				break;

				case 's':
				case 'survival':
					if($sender->hasPermission(Constants::SURVIVAL))
						$sender->setGamemode(PMGameMode::SURVIVAL());
					else {
						$sender->sendMessage(Constants::NO_PERMISSION);
						return;
					}
				break;
				
				case 'spec':
				case 'spectator':
					if($sender->hasPermission(Constants::SPECTATE))
						$sender->setGamemode(PMGameMode::SPECTATOR());
					else {
						$sender->sendMessage(Constants::NO_PERMISSION);
						return;
					}
				break;

				default:
					throw new InvalidCommandSyntaxException();
			}
			$sender->sendMessage(Constants::GAMEMODE_MESSAGE);
		} else {
            throw new InvalidCommandSyntaxException();
        }

	}

}