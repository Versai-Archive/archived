<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/22/2019
 * Time: 8:17 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Party;

use pocketmine\Player;

use ARTulloss\Duels\Match\Task\Heartbeat;
use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Duels;

/**
 * Class PartySpectate
 * @package ARTulloss\Duels\Commands\Sub\Party
 */
class PartySpectate extends SubCommand
{
	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);

		if($party !== null) {

			$duels = $this->command->getPlugin();

			if (!$duels instanceof Duels)
				return;

			foreach ($party->getPlayers() as $player) {

				$match = $duels->duelManager->getPlayersMatch($player);

				if($match === null)
					continue;

				if($match->getStage() === Heartbeat::STAGE_FINISHED)
					$sender->sendMessage(Constants::SPECTATE_FINISHED);
				else {
					if(isset($match->getSpectators()[$player->getName()]))
						$player->sendMessage(Constants::SPECTATE_ALREADY);
					else
						$match->addSpectator($sender);
					return;
				}
			}
			$sender->sendMessage(Constants::NONE_IN_MATCH);
		} else
			$sender->sendMessage(Constants::NOT_IN_PARTY_SELF);
	}
}