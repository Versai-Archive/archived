<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;

class PartyDisband extends SubCommand {

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