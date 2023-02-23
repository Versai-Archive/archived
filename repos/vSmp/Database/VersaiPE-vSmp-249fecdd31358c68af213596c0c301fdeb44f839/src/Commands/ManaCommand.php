<?php

declare(strict_types = 1);

/**
 * This file is a command for testing mana
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Commands;

use Versai\RPGCore\Main;

use pocketmine\Server;
use pocketmine\permission\DefaultPermissions;
use pocketmine\command\{
	Command,
	CommandSender
};
use pocketmine\plugin\{
	PluginOwned,
	PluginOwnedTrait
};

class ManaCommand extends Command implements PluginOwned {

    use PluginOwnedTrait;

	/**
	* ManaCommand Constructor.
	*
	* @param string $name
	* @param string $description
	**/
	public function __construct($name, $description) {
        parent::__construct($name, $description);
    }

	/**
	* @param CommandSender $sender
	* @param string        $commandLabel
	* @param array         $args
	* @return bool|mixed
	**/
	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
			$sender->sendMessage("§d[SYSTEM] §cNo authorization detected");
            $sender->sendMessage("§d[SYSTEM] §cYou may not use command: mana");
            return false;
		}
		if (!isset($args[0])) {
			$sender->sendMessage("§cUsage: /mana <set:reset:charge> [amount] [player]");
			return false;
		}
		switch($args[0]) {
			case 'set':
				if(!isset($args[1])) {
					$sender->sendMessage("§cUsage: /mana <set:reset:charge> [amount] [player]");
					return false;
				}
				$amount = (int)$args[1];
				if(!isset($args[2])) { // If player argument isn't set
					if($amount > 20) { // If specified amount is greater than 20
						$sender->sendMessage("§cThe amount must be under 20!");
						return false;
					}
					if($amount < 0) { // If specified amount is less than 0
						$sender->sendMessage("§cThe amount cannot be negative!");
						return false;
					}
					$sender->setMana($amount);
					$sender->sendMessage("§aSuccessfully set your mana to ".$args[1]);
					return true;
				}
				$target = Server::getInstance()->getPlayerByPrefix($args[2]);
				if($target === null) {
					$sender->sendMessage("§cPlayer provided is not online!");
					return false;
				}
				$target->setMana($amount);
				$target->sendMessage("§aYour mana has been set to ".$args[1]);
				return true;
			break;
			
			case 'reset':
				if(!isset($args[2])) { // If player argument isn't set
					$sender->resetMana();
					$sender->sendMessage("§aSuccessfully reset your mana!");
					return true;
				}
				$target = Server::getInstance()->getPlayerByPrefix($args[2]);
				if($target === null) {
					$sender->sendMessage("§cPlayer provided is not online!");
					return false;
				}
				$target->resetMana();
				$target->sendMessage("§aYour mana has reset!");
				return true;
			break;
			
			case 'charge':
				if(!isset($args[2])) { // If player argument isn't set
					$sender->chargeMana();
					$sender->sendMessage("§aSuccessfully charged your mana!");
					return true;
				}
				$target = Server::getInstance()->getPlayerByPrefix($args[2]);
				if($target === null) {
					$sender->sendMessage("§cPlayer provided is not online!");
					return false;
				}
				$target->chargeMana();
				$target->sendMessage("§aYour mana has been charged!");
				return true;
			break;
			
			default:
				$sender->sendMessage("§cInsufficient argument! (Only set, reset, or charge allowed.)");
				return true;
		}
	}
}