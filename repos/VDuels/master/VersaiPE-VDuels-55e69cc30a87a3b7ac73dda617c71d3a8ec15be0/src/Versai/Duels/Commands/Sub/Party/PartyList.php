<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\Sub\BackSubCommand;
use jojoe77777\FormAPI\SimpleForm;
use Versai\Duels\Party\Party;
use function count;
use function str_replace;

class PartyList extends BackSubCommand {

	private const TEXT_FORMAT = 'Leader: {leader} Players: {players} Code: {code}';
	private const BUTTON_FORMAT = "Leader: {leader}\nPlayers: {players} Code: {code}";
	private const BUTTON_FORMAT_OWN = TextFormat::GOLD . "Leader: {leader}\nPlayers: {players} Code: {code}";

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		if(isset($args[0])) {
			if($args[0] === '-c') {
                $sender->sendMessage('The following parties are open');
            }

			foreach ($this->manager->getAllParties() as $party) {
                if ($party->isPublic()) {
                    $sender->sendMessage(str_replace(['{leader}', '{code}'], [$party->getLeader()->getName(), $party->getCode()], self::TEXT_FORMAT));
                }
            }
		} else {
            $this->sendForm($sender);
        }
	}

	/**
	 * @param Player $player
	 */
	public function sendForm(Player $player): void {

		$parties = $this->manager->getAllParties();
		$openParties = [];

		foreach ((array)$parties as $party) {
            if ($party->isPublic()) {
                $openParties[] = $party;
            }
        }

		$back = $this->hasBack();

		$senderParty = $this->manager->getPartyForPlayer($player);

		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data) use ($openParties, $senderParty, $back): void
		{
			if(isset($data)) {
					$senderName = $player->getDisplayName();

					/** @var Party $party */
					$openParties = array_values($openParties);

				//	var_dump($openParties);
					if(isset($openParties[$data])) {
						if($senderParty !== null) {
                            if ($senderParty === $openParties[$data]) {
                                $player->sendMessage(Constants::ALREADY_IN_YOUR_PARTY);
                            } else {
                                $player->sendMessage(Constants::ALREADY_IN_PARTY);
                            }
                        } else {
							$party = $openParties[$data];
							$leaderName = $party->getLeader()->getDisplayName();
							$party->sendMessageToAll(str_replace('{player}', $senderName, Constants::JOINED_PARTY));
							$party->addPlayer($player);
							$player->sendMessage(str_replace('{leader}', $leaderName, Constants::JOINED_PARTY_SELF));
						}
					} elseif($back) {
                        $this->goBack($player);
                    }
				}
		};

		$form = new SimpleForm($callable);

		$form->setTitle('Open party list, tap to join!');

		foreach ($openParties as $party) {
            if ($senderParty === $party) {
                $form->addButton(str_replace(['{leader}', '{code}', '{players}'], [$party->getLeader()->getDisplayName(), $party->getCode(), count($party->getPlayers())], self::BUTTON_FORMAT_OWN));
            } else {
                $form->addButton(str_replace(['{leader}', '{code}', '{players}'], [$party->getLeader()->getDisplayName(), $party->getCode(), count($party->getPlayers())], self::BUTTON_FORMAT));
            }
        }

		if($openParties === []) {
            $form->addButton(Constants::NO_PARTIES_OPEN, Constants::BACK_TYPE, Constants::BACK_IMAGE);
        } elseif($back) {
            $form->addButton(Constants::BACK, Constants::BACK_TYPE, Constants::BACK_IMAGE);
        }

		$player->sendForm($form);

	}

}