<?php

declare(strict_types = 1);

/**
 * This file is a command for testing stats
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
use pocketmine\player\Player;

use Versai\RPGCore\Libraries\FormAPI\window\SimpleWindowForm;
use Versai\RPGCore\Libraries\FormAPI\elements\Button;


class StatCommand extends Command implements PluginOwned {

    use PluginOwnedTrait;

	private $plugin;
	
	/**
	* StatCommand Constructor.
	*
	* @param string $name
	* @param string $description
	**/
	public function __construct($name, $description, Main $plugin) {
        parent::__construct($name, $description);
		$this->plugin = $plugin;
    }

	/**
	* @param CommandSender $sender
	* @param string        $commandLabel
	* @param array         $args
	* @return bool|mixed
	**/
	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		$prefix = "§7[§2RPG§aCore§7]";

	    if(!$sender instanceof Player) {
            $sender->sendMessage("$prefix §cThis command is only available in-game.");
            return false;
        }
		if(!$sender->hasPermission("rpg.command.stat")) {
			$sender->sendMessage("$prefix §cNo authorization detected");
            $sender->sendMessage("$prefix §cYou may not use command: stat");
            return false;
		}
		if (!isset($args[0])) {
			$sender->sendMessage("$prefix §cYou must provide a valid argument!");
			return false;
		}
		switch ($args[0]) {
			case 'manage':
				switch($args[1]) {
					case 'agility':
						if (!isset($args[1])) {
							$sender->sendMessage("$prefix You must choose a value to set this too");
							return false;
						}
						$sender->setAgility((float)$args[2]);
						$sender->sendMessage("$prefix §aValue set to ".$args[1]);
					break;
					
					case 'vitality':
						if (!isset($args[1])) {
							$sender->sendMessage("$prefix §cYou must choose a value to set this too");
							return false;
						}
						$sender->setVitality((int)$args[2]);
						$sender->sendMessage("$prefix §aValue set to ".$args[1]);
					break;
				}
			
			case 'view':
				if(!isset($args[1])) {
					return $sender->sendMessage("$prefix §cThe proper command usage is /stat <manage:view> <player:stat>");
				} else {
					$target = $sender->getServer()->getPlayerByPrefix($args[1]);
					if (!$target) {
						return $sender->sendMessage("$prefix §cThis player could not be located");
					} else {
						$sessionManager = $this->plugin->getSessionManager();
						$playerSession = $sessionManager->getSession($target);
						$targetMana = $playerSession->getMaxMana();
						$targetHealth = $target->getMaxHealth();
						$targetAgility = $playerSession->getAgility();

						$sender->sendMessage("Stats for {$target->getName()}: \n §9Mana§7: §c$targetMana \n §4Health§7: §c$targetHealth \n §3Agility§7: §c$targetAgility");
					}
				}
			break;
			default:
			$sender->sendMessage("$prefix §cThe proper command usage is /stat <manage:view> <player:stat>");
		}
		return true;
	}
}