<?php


namespace Martin\SkyBlock\command\subcommand\job;


use Martin\SkyBlock\command\SubCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class JobListCommand extends SubCommand{

	public function onConsoleCommand(ConsoleCommandSender $sender, array $args) : void{
		$this->sendMessage($sender);
	}

	private function sendMessage(CommandSender $sender) : void{
		$sender->sendMessage("Job List:");
		foreach($this->getJobs() as $job){
			$sender->sendMessage(TextFormat::BLUE . $job);
		}
	}

	private function getJobs() : array{
		return [
			"Unemployed",
			"Butcher",
			"Miner",
			"Farmer",
			"Lumberjack"
		];
	}

	public function onCommand(Player $sender, array $args) : void{
		$this->sendMessage($sender);
	}
}