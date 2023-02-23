<?php


namespace Martin\SkyBlock\command\subcommand\island;


use Martin\SkyBlock\command\SubCommand;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

class CreateCommand extends SubCommand{
	public function onConsoleCommand(ConsoleCommandSender $sender, array $args) : void{
		$sender->sendMessage("Error!");
	}

	public function onCommand(Player $sender, array $args) : void{
		if(!$this->getLoader()->getIslandManager()->canAddIsland($sender->getName())){
			# Error: Maximum amount of islands reached
			return;
		}

		$island = $this->getLoader()->getIslandManager()->createIsland($sender->getName());
		# Successfully created an new island -> teleport to it
	}
}