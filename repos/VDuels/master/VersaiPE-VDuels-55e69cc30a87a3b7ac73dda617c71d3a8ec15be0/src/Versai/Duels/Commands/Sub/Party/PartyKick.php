<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;
use Versai\Duels\Utilities\Utilities;

class PartyKick extends SubCommand {

	private const ACTION = 'kick';

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		if(isset($args[0])) {

			$party = $this->manager->getPartyForPlayer($sender);

			$player = Utilities::getPlayerCommand($sender, $args[0]);

			if($player === null) {
                return;
            }

			if($party === null) {
                $sender->sendMessage(Constants::NOT_IN_PARTY_SELF);
            } else {

				if($sender === $player) {
                    $sender->sendMessage(str_replace('{action}', self::ACTION, Constants::ACTION_SELF));
                } else {

					if($this->manager->getPartyForPlayer($player) === $party) {

						$party->removePlayer($player);
						$player->sendMessage(Constants::KICKED_FROM_PARTY);
						$party->sendMessageToAll(str_replace('{player}', $player->getDisplayName(), Constants::LEFT_PARTY));

					} else {
                        $sender->sendMessage(Constants::NOT_IN_YOUR_PARTY);
                    }
				}
			}

		}
	}

}