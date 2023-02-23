<?php

declare(strict_types=1);

namespace lastseen;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class LastSeenCommand extends Command implements PluginIdentifiableCommand{
	/** @var Loader */
	private $plugin;

	/**
	 * LastSeenCommand constructor.
	 *
	 * @param string $name
	 * @param Loader $plugin
	 */
	public function __construct(string $name, Loader $plugin){
		parent::__construct($name);
		$this->setDescription("see the last time a player was on the server!");
		$this->setPermission(Loader::PERMISSION);
		$this->plugin = $plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($this->testPermission($sender)){
			if(isset($args[0])){
				$this->plugin->getTime($args[0], static function(?string $time) use($sender, $args) : void{
					if($time !== null){
						$sender->sendMessage(TextFormat::AQUA . "[LastSeen]" . TextFormat::RESET . $args[0] . TextFormat::GREEN . " was last seen at " . $time);
					    return;
					}else{
						$sender->sendMessage(TextFormat::RED . "[LastSeen] {$args[0]} was not found!");
						return;
					}
				});
			}else{
				$sender->sendMessage("Usage: /ls <player username>");
				return;
			}
		}else{
			$sender->sendMessage(TextFormat::RED . "You don't have permission to run this command!");
			return;
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}
}