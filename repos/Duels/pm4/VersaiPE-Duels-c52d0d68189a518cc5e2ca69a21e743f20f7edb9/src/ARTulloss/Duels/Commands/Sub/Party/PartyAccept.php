<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/9/2019
 * Time: 8:03 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Party;

use pocketmine\Player;

use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Commands\Constants;

use function array_search;
use function str_replace;

/**
 * Class PartyInvite
 * @package ARTulloss\Duels\Commands\PartySub
 */
class PartyAccept extends SubCommand
{
	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$senderName = $sender->getDisplayName();

		if($this->manager->getPartyFor($senderName) !== null)
			$sender->sendMessage(Constants::ALREADY_IN_PARTY);
		else {
			$leaderName = array_search($sender, (array) $this->command->invitedPlayers, true);

			if($leaderName !== false) {

				unset($this->command->invitedPlayers[$leaderName]); // Remove from invited

				$party = $this->manager->getPartyFor($leaderName);

				if($party === null)
					$sender->sendMessage(Constants::GLITCH);
				else {
					$party->sendMessageToAll(str_replace('{player}', $senderName, Constants::JOINED_PARTY));
					$party->addPlayer($sender);
					$sender->sendMessage(str_replace('{leader}', $leaderName, Constants::JOINED_PARTY_SELF));
				}
			}
		}

	}
}