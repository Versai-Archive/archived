<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Party;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\Sub\BackSubCommand;
use jojoe77777\FormAPI\SimpleForm;
use Versai\Duels\Utilities\Utilities;

class PartyPromote extends BackSubCommand {

	private const ACTION = 'promote';

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$senderParty = $this->manager->getPartyForPlayer($sender);

		if($senderParty === null) {
			$sender->sendMessage(Constants::NOT_IN_PARTY_SELF);
			return;
		}

	//	var_dump($args);

		if(isset($args[0])) {

		//	var_dump($args[0]);

			$player = Utilities::getPlayerCommand($sender, $args[0]);

			if($player === null) {
				return; // Utility handles message for offline
			}

			if($sender === $player) {
				$sender->sendMessage(str_replace('{action}', self::ACTION, Constants::ACTION_SELF));
				return;
			}

			$playerParty = $this->manager->getPartyForPlayer($player);

			if($playerParty === null) {
				$sender->sendMessage(Constants::NOT_IN_YOUR_PARTY);
				return;
			}

			if($senderParty === $playerParty) {

				if($sender !== $senderParty->getLeader()) {
                    $sender->sendMessage(Constants::MUST_BE_LEADER);
                } else {
					$this->manager->promotePlayer($player, $senderParty);
					$senderParty->sendMessageToAll(str_replace('{player}', $player->getDisplayName(), Constants::PROMOTION));
				}

			} else {
                $sender->sendMessage(TextFormat::RED . $player->getDisplayName() . ' isn\'t in your party!');
            }
		} else {

			$players = $senderParty->getPlayers();

			$back = $this->hasBack();

			if($back) {
                unset($players[$sender->getDisplayName()]);
            }

			/**
			 * @param Player $player
			 * @param int|null $data
			 */
			$callable = function (Player $player, ?int $data) use ($players, $back): void {
				if(isset($data)) {
					/** @var Player[] $players */
					$players = array_values($players);
					if (isset($players[$data])) {
						$args[0] = $players[$data]->getDisplayName();
						$this->execute($player, $args);
					} elseif($back) {
                        $this->goBack($player);
                    }
				}
			};

			$form = new SimpleForm($callable);

			$leader = $senderParty->getLeader();

			foreach ($players as $player) {
                if ($player === $leader) {
                    $form->addButton(str_replace('{player}', $player->getDisplayName(), Constants::PARTY_LEADER_FORMAT));
                } else {
                    $form->addButton($player->getDisplayName());
                }
            }

			if($back) {
                if (count($players) === 0) {
                    $form->addButton(Constants::YOU_ARE_ONLY_PLAYER, Constants::BACK_TYPE, Constants::BACK_IMAGE);
                } else {
                    $form->addButton(Constants::BACK, Constants::BACK_TYPE, Constants::BACK_IMAGE);
                }
            }

			$form->setTitle('Promote');
			$sender->sendForm($form);

		}
	}
}