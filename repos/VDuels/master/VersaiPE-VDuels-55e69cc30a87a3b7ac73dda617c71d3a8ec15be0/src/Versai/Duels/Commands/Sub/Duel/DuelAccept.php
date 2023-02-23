<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Duel;

use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;
use Versai\Duels\Duels;
use function array_search;
use function explode;
use function in_array;


class DuelAccept extends SubCommand{

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void{
		$askedForDuel = $this->command->getAskedForDuel();
		if (in_array($sender, $askedForDuel, true)) {
			$key = array_search($sender, $askedForDuel, true);
			$explosion = explode(':', $key);
			$duels = Duels::getInstance();
			$player = $duels->getServer()->getPlayerExact($explosion[0]);
			if($player !== null) {
                $duels->queueManager->removePlayerFromQueue($player, false);
                $duels->duelManager->createMatch([$sender, $player], $explosion[1], $explosion[2]);
                unset($askedForDuel[$key]);
                $this->command->setAskedForDuel($askedForDuel);
            }
		} else {
            $sender->sendMessage(Constants::NO_PENDING_DUEL_REQUESTS);
        }
	}
}