<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/3/2019
 * Time: 12:34 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Party;

use ARTulloss\Duels\Party\Party;
use pocketmine\Player;

use jojoe77777\FormAPI\SimpleForm;

use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Commands\Sub\BackSubCommand;

use function str_replace;
use function array_values;

/**
 * Class PartyListPlayers
 * @package ARTulloss\Duels\Commands\Sub\Party
 */
class PartyListPlayers extends BackSubCommand
{
	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);

		if($party !== null) {
			if($party->getLeader() === $sender)
				$this->listPlayersInParty($sender, $party,true);
			else
				$this->listPlayersInParty($sender, $party);
		} else
			$sender->sendMessage(Constants::NOT_IN_PARTY_SELF);
	}

	/**
	 * @param Player $player
	 * @param Party $party
	 * @param bool $isOwner
	 */
	public function listPlayersInParty(Player $player, Party $party, bool $isOwner = false): void
	{
		/** @var Player[] $players */
		$players = array_values($party->getPlayers());

		$back = $this->hasBack();

		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data) use ($party, $players, $isOwner, $back): void
		{
			if(isset($data)) {

				if($isOwner) {

					if(isset($players[$data])) {

						if($player === $players[$data])
							$player->sendMessage(Constants::KICK_SELF);
						else {
							$party->removePlayer($players[$data]);
							$players[$data]->sendMessage(Constants::KICKED_FROM_PARTY);
							$party->sendMessageToAll(str_replace('{player}', $players[$data]->getDisplayName(), Constants::LEFT_PARTY));
						}

					} elseif($back)
						$this->goBack($player);

				} elseif($back) {

					if($isOwner)
						$this->command->sendPartyOwnerForm($player);
					else
						$this->command->sendInPartyForm($player);
				}
			}
		};

		$form = new SimpleForm($callable);

		$form->setTitle('Party Members');

		$leader = $party->getLeader();

		foreach ($players as $p)
			if($p === $leader)
				$form->addButton(str_replace('{player}', $p->getDisplayName(), Constants::PARTY_LEADER_FORMAT));
			else
				$form->addButton($p->getDisplayName());

		if($back)
			$form->addButton(Constants::BACK, Constants::BACK_TYPE, Constants::BACK_IMAGE);

		$player->sendForm($form);

	}

}