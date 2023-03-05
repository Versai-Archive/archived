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
 * Class PartyDisband
 * @package ARTulloss\Duels\Commands\PartySub
 */
class PartyDisband extends SubCommand
{
	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);
		if($this->manager->checkPartyLeader($sender, $party)) {
			$sender->sendMessage(Constants::DISBAND_PARTY_SELF);
			$party->sendMessageToNonLeader(Constants::DISBAND_PARTY);
			$this->manager->disbandParty($party);
		}
	}
}