<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/10/2019
 * Time: 5:51 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Party;

use pocketmine\Player;

use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Utilities\Utilities;

/**
 * Class PartyKick
 * @package ARTulloss\Duels\Commands\Sub\Party
 */
class PartyKick extends SubCommand
{
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

			if($player === null)
				return;

			if($party === null)
				$sender->sendMessage(Constants::NOT_IN_PARTY_SELF);
			else {

				if($sender === $player)
					$sender->sendMessage(str_replace('{action}', self::ACTION, Constants::ACTION_SELF));
				else {

					if($this->manager->getPartyForPlayer($player) === $party) {

						$party->removePlayer($player);
						$player->sendMessage(Constants::KICKED_FROM_PARTY);
						$party->sendMessageToAll(str_replace('{player}', $player->getName(), Constants::LEFT_PARTY));

					} else
						$sender->sendMessage(Constants::NOT_IN_YOUR_PARTY);

				}
			}

		}
	}

}