<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;
use function str_replace;

class PartyCreate extends SubCommand {

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		if($this->manager->getPartyFor($sender->getName()) === null) {
			$code = $this->manager->createParty($sender)->getCode();
			$sender->sendMessage(str_replace('{code}', $code, Constants::CREATE_PARTY));
		} else {
            $sender->sendMessage(Constants::ALREADY_IN_PARTY);
        }
	}
}