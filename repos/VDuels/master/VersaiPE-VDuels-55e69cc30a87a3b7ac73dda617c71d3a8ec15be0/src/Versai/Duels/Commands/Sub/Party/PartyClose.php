<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;
use function str_replace;

class PartyClose extends SubCommand {

	private const STATE = 'close';

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);

		if($this->manager->checkPartyLeader($sender, $party)) {

			if(!$party->isPublic()) {
                $sender->sendMessage(str_replace('{state}', self::STATE, Constants::ALREADY_STATE) . 'd!');
            } else {
				$sender->sendMessage(str_replace('{state}', self::STATE, Constants::NOW_STATE . 'd!'));
				$party->setPublic(false);
			}

		}

	}
}