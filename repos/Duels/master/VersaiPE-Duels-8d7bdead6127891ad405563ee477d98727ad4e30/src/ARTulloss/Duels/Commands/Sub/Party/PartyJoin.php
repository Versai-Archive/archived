<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/9/2019
 * Time: 7:58 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Party;

use pocketmine\Player;

use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Commands\Constants;

/**
 * Class PartyJoin
 * @package ARTulloss\Duels\Commands\PartySub
 */
class PartyJoin extends SubCommand
{
	private const TYPE = 'code';

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$senderName = $sender->getDisplayName();

		if(!isset($args[0]))
			$sender->sendMessage(str_replace('{type}', self::TYPE, Constants::MUST_ENTER));
		else {
			$playersParty = $this->manager->getPartyForPlayer($sender);

			if($playersParty !== null)
				$sender->sendMessage(Constants::ALREADY_IN_PARTY);
			else {
				$party = $this->manager->getPartyByCode($args[0]);

				if($party === null)
					$sender->sendMessage(Constants::PARTY_NOT_EXIST);
				else {
					$party->sendMessageToAll(str_replace('{player}', $senderName, Constants::JOINED_PARTY));
					$party->addPlayer($sender);
					$sender->sendMessage(str_replace('{leader}', $party->getLeader()->getDisplayName(), Constants::JOINED_PARTY_SELF));
				}
			}

		}
	}

}