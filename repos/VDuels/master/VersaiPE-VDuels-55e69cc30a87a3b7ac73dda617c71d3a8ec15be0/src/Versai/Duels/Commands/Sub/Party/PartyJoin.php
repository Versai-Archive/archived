<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;

class PartyJoin extends SubCommand {

	private const TYPE = 'code';

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$senderName = $sender->getDisplayName();

		if(!isset($args[0])) {
            $sender->sendMessage(str_replace('{type}', self::TYPE, Constants::MUST_ENTER));
        } else {
			$playersParty = $this->manager->getPartyForPlayer($sender);

			if($playersParty !== null) {
                $sender->sendMessage(Constants::ALREADY_IN_PARTY);
            } else {
				$party = $this->manager->getPartyByCode($args[0]);

				if($party === null) {
                    $sender->sendMessage(Constants::PARTY_NOT_EXIST);
                } else {
					$party->sendMessageToAll(str_replace('{player}', $senderName, Constants::JOINED_PARTY));
					$party->addPlayer($sender);
					$sender->sendMessage(str_replace('{leader}', $party->getLeader()->getDisplayName(), Constants::JOINED_PARTY_SELF));
				}
			}

		}
	}

}