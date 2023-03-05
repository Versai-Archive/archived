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
 * Class PartyClose
 * @package ARTulloss\Duels\Commands\PartySub
 */
class PartyClose extends SubCommand
{
	private const STATE = 'close';

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);

		if($this->manager->checkPartyLeader($sender, $party)) {

			if(!$party->isPublic())
				$sender->sendMessage(str_replace('{state}', self::STATE, Constants::ALREADY_STATE) . 'd!');
			else {
				$sender->sendMessage(str_replace('{state}', self::STATE, Constants::NOW_STATE . 'd!'));
				$party->setPublic(false);
			}

		}

	}
}