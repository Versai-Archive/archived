<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;
use Versai\Duels\Duels;
use Versai\Duels\Match\Task\Heartbeat;

class PartySpectate extends SubCommand {

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);

		if($party !== null) {

			$duels = Duels::getInstance();
			if (!$duels instanceof Duels) {
                return;
            }

			foreach ($party->getPlayers() as $player) {
				$match = $duels->duelManager->getPlayersMatch($player);

				if($match === null) {
                    continue;
                }

				if($match->getStage() === Heartbeat::STAGE_FINISHED) {
                    $sender->sendMessage(Constants::SPECTATE_FINISHED);
                } else {
					if(isset($match->getSpectators()[$player->getName()])) {
                        $player->sendMessage(Constants::SPECTATE_ALREADY);
                    } else {
                        $match->addSpectator($sender);
                    }
					return;
				}
			}
			$sender->sendMessage(Constants::NONE_IN_MATCH);
		} else {
            $sender->sendMessage(Constants::NOT_IN_PARTY_SELF);
        }
	}
}