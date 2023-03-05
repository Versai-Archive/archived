<?php
declare(strict_types=1);

namespace Versai\miscellaneous\commands;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use Versai\miscellaneous\Constants;

class Ping extends Command{

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(isset($args[0])) {
			$player = $sender->getServer()->getPlayerByPrefix($args[0]);

			if($player === null) {
				$sender->sendMessage(str_replace('{player}', $args[0], Constants::PLAYER_OFFLINE));
				return;
			}

			$sender->sendMessage(str_replace(['{player}', '{ping}'], [$player->getName(), strval($player->getNetworkSession()->getPing())], Constants::PING_OTHER_FORMAT));
		} elseif($sender instanceof Player) {
            $sender->sendMessage(str_replace('{ping}', strval($sender->getNetworkSession()->getPing()), Constants::PING_SELF_FORMAT));
        }else {
            $sender->sendMessage(Constants::PLAYER_ONLY);
        }
	}
}