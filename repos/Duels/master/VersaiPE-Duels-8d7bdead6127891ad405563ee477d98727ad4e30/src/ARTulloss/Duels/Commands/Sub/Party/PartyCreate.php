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

use function str_replace;

/**
 * Class PartyCreate
 * @package ARTulloss\Duels\Commands\PartySub
 */
class PartyCreate extends SubCommand
{
	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		if($this->manager->getPartyFor($sender->getName()) === null) {
			$code = $this->manager->createParty($sender)->getCode();
			$sender->sendMessage(str_replace('{code}', $code, Constants::CREATE_PARTY));
		} else
			$sender->sendMessage(Constants::ALREADY_IN_PARTY);
	}
}