<?php


namespace Martin\SkyBlock\command\subcommand\job;


use Martin\SkyBlock\command\SubCommand;
use Martin\SkyBlock\constants\JobList;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SelectJobCommand extends SubCommand{

	public function onConsoleCommand(ConsoleCommandSender $sender, array $args) : void{
		// TODO: Implement onConsoleCommand() method.
	}

	public function onCommand(Player $sender, array $args) : void{
		$playerSession = $this->getLoader()->getPlayerManager()->getSession($sender);
		if($playerSession){
			$sender->kick(TextFormat::RED . "Error: Player Session was never initalized");
			return;
		}

		if($playerSession->getJob() !== JobList::UNEMPLOYED){
			$sender->sendMessage($this->getMessage("commands.job.select.not-unemployed"));
			return;
		}

		if(!$playerSession->canSwitchJobs()){
			$sender->sendMessage($this->getMessage("commands.job.select.on-cooldown"));
			return;
		}
	}
}