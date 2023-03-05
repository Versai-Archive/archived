<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/3/2019
 * Time: 11:30 AM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Party;

use pocketmine\Player;

use ARTulloss\Duels\libs\jojoe77777\FormAPI\SimpleForm;
use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Commands\Sub\BackSubCommand;
use ARTulloss\Duels\Duels;
use ARTulloss\Duels\Queue\QueueManager;
use ARTulloss\Kits\Kits;
use Throwable;

/**
 * Class PartyDuel
 * @package ARTulloss\Duels\Commands\Sub\Party
 */
class PartyDuel extends BackSubCommand
{
	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$party = $this->manager->getPartyForPlayer($sender);
		if($this->manager->checkPartyLeader($sender, $party))
			$this->sendPartyDuelForm($sender);
	}

	/**
	 * @param Player $leader
	 */
	public function sendPartyDuelForm(Player $leader): void{
		$kits = Kits::getInstance();
		$kitTypes = $kits->kitTypes;
		$back = $this->hasBack();
		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data) use ($kitTypes, $back): void {
			if (isset($data)) {
				$kitTypes = array_values($kitTypes);
				if(isset($kitTypes[$data])) {

					$type = $kitTypes[$data];

					$party = $this->manager->getPartyForPlayer($player);

					$duels = Duels::getInstance();

					$duelManager = $duels->duelManager;

					foreach ($party->getPlayers() as $p) {
						$match = $duelManager->getPlayersMatch($p);
						if($match !== null) {
							$match->stopMatch();
							$party->sendMessageToNonLeader(Constants::LEADER_ENDED_MATCH);
							$player->sendMessage(Constants::LEADER_ENDED_MATCH_SELF);
							break;
						}
					}
					$levels = $duels->levels;
                    $allKitIds = Kits::getInstance()->kitIDs;
                    $kitId = $allKitIds[$type];
                    /**
                     * @param Player $player
                     * @param $data
                     */
					$callable = function (PLayer $player, $data) use ($party, $type, $levels, $duelManager, $kitTypes, $kitId): void{
					    if(isset($data)) {
                            foreach ($levels as $key => $level) {
                                if (!in_array($kitId, $level->getIDs()))
                                    unset($levels[$key]);
                            }
                            if ($data > count($levels)) { // Back button
                                $this->sendPartyDuelForm($player);
                                return;
                            }
                            $map = $data === 0 ? 'Random' : array_values($levels)[--$data]->getName();
                            $duelManager->createMatch($party->getPlayers(), $type, $map, QueueManager::UNRANKED, $party);
                        }
                    };

					$form = new SimpleForm($callable);
					$form->setTitle('Select an Arena');
					$form->addButton('Random');
					foreach ($levels as $level)
                        if(in_array($kitId, $level->getIDs()))
                            $form->addButton($level->getName());
                    $form->addButton(Constants::BACK, Constants::BACK_TYPE, Constants::BACK_IMAGE);
                    $player->sendForm($form);
				} elseif($back)
					$this->goBack($player);
			}
		};

		$form = new SimpleForm($callable);

		$form->setTitle('Party Duel!');

		foreach ($kitTypes as $kitType) {
			$lKitType = strtolower($kitType);
			$kitValues = array_values($kits->kits[$lKitType]);
			$form->addButton($kitType, $kitValues[0]->getImageType(), $kitValues[0]->getURL());
		}

		if($back)
			$form->addButton(Constants::BACK, Constants::BACK_TYPE, Constants::BACK_IMAGE);

		$leader->sendForm($form);
	}

}