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

/**
 * Class PartyLeave
 * @package ARTulloss\Duels\Commands\PartySub
 */
class PartyLeave extends SubCommand
{
	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);
		$senderName = $sender->getName();

		if($party !== null) {
			if ($sender === $party->getLeader()) {
				$sender->sendMessage(Constants::LEFT_PARTY_OWNER_SELF);
				$party->sendMessageToNonLeader(Constants::LEFT_PARTY_OWNER);
				$this->manager->disbandParty($party); // Disband party has messages
			} else {
				$sender->sendMessage(Constants::LEFT_PARTY_SELF);
				$party->removePlayer($sender);
				$party->sendMessageToAll(str_replace('{player}', $senderName, Constants::LEFT_PARTY));
			}
		} else
			$sender->sendMessage(Constants::NOT_IN_PARTY_SELF);

	}
}