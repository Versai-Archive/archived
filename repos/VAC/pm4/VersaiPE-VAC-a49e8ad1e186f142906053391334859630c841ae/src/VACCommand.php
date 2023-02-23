<?php

namespace ethaniccc\VAC;

use ethaniccc\VAC\data\DataHandler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class VACCommand extends Command implements PluginOwned {

	use PluginOwnedTrait;

	public function __construct() {
		parent::__construct("vac", "The command for the Versai anti-cheat", "/vac <subcommand> <other_args", []);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		$sub = array_shift($args);
		if ($sub === null) {
			$sender->sendMessage(VAC::getInstance()->getPrefix() . " Versai AC");
			return;
		}
		if (!$sender->hasPermission("ac.command.$sub")) {
			$sender->sendMessage(VAC::getInstance()->getPrefix() . TextFormat::RED . " You do not have permission to use this command");
			return;
		}
		switch ($sub) {
			case "cooldown":
				if (!$sender instanceof Player) {
					$sender->sendMessage(TextFormat::RED . "You cannot run this command in the CONSOLE");
					return;
				}
				$cooldown = array_shift($args);
				if ($cooldown === null) {
					$sender->sendMessage(TextFormat::RED . "You need to supply a cooldown value");
					return;
				}
				VAC::getInstance()->setCooldown($sender->getName(), (float)$cooldown);
				$sender->sendMessage(VAC::getInstance()->getPrefix() . " Your cooldown has been set to " . TextFormat::GREEN . $cooldown . TextFormat::RESET . " seconds");
				break;
			case "logs":
				$target = array_shift($args);
				if ($target === null) {
					$sender->sendMessage(TextFormat::RED . "You need to specify a target to get the logs of");
					return;
				}
				$target = Server::getInstance()->getPlayer($target);
				if ($target === null) {
					$sender->sendMessage(TextFormat::RED . "Specified target was not found on the server");
					return;
				}
				$logs = "";
				$data = DataHandler::getInstance()->get($target);
				if ($data === null) {
					$sender->sendMessage(TextFormat::RED . "ERROR: Specified target is not listed in the data handler");
					return;
				}
				foreach ($data->detections as $detection) {
					if ($detection->getViolations() >= 1) {
						$logs .= TextFormat::AQUA . $detection->getCategory() .
							TextFormat::GRAY . " (" . TextFormat::AQUA . $detection->getSubCategory() . TextFormat::GRAY . ") " . TextFormat::WHITE .
							" - " . $detection->getDescription() . " " . TextFormat::GRAY . "(" . TextFormat::RED . "x" . var_export(round($detection->getViolations(), 2), true) .
							TextFormat::GRAY . ")\n";
					}
				}
				if ($logs === "") {
					$sender->sendMessage(VAC::getInstance()->getPrefix() . " No logs found for " . TextFormat::AQUA . $target->getName());
				} else {
					$sender->sendMessage(VAC::getInstance()->getPrefix() . " Logs for " . TextFormat::AQUA . $target->getName() . TextFormat::RESET . ":\n$logs");
				}
				break;
		}
	}

	public function getPlugin(): Plugin {
		return VAC::getInstance();
	}

}