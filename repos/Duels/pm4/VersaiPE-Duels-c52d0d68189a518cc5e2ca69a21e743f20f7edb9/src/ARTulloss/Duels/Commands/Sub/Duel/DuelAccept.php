<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/3/2019
 * Time: 3:34 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Duel;

use pocketmine\player\Player;

use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Duels;

use function in_array;
use function array_search;
use function explode;

/**
 * Class DuelAccept
 * @package ARTulloss\Duels\Commands\Sub\Duel
 */
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
		} else
			$sender->sendMessage(Constants::NO_PENDING_DUEL_REQUESTS);
	}

}