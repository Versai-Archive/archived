<?php
declare(strict_types=1);

namespace Versai\Duels\Commands\Sub\Duel;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Commands\SubCommand;
use Versai\Duels\Duels;
use jojoe77777\FormAPI\SimpleForm;
use Versai\Duels\Queue\QueueManager;
use Duo\kits\Kit;
use Duo\kits\Kits;
use function array_values;
use function implode;
use function str_replace;
use function strtolower;

class DuelQueue extends SubCommand {

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	public function execute(Player $sender, array $args): void
	{
		$this->rankMenu($sender);
	}

	/**
	 * @param Player $player
	 */
	public function rankMenu(Player $player): void
	{
		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data): void {
		    if($data === null){
		        return;
            }

            switch($data){
                case 0:
                case 1:
                case 3:
                    $this->queueTypeMenu($player, $data);
                    break;
                case 2:
                    Duels::getInstance()->getServer()->getCommandMap()->dispatch($player, "botduel");
                    break;
            }
		};

		$form = new SimpleForm($callable);

		$form->setTitle("Choose a Duel Type!");

		/** @var Duels $duels */
		$duels = Duels::getInstance();

		$kitTypes = Kits::getInstance()->kitTypes;

        $form->addButton("Ranked" . "\n" . str_replace(['{playing}', '{queued}'], [$duels->duelManager->getRunningTypes($kitTypes, QueueManager::RANKED), $duels->queueManager->getInQueueTypes($kitTypes, QueueManager::RANKED)], Constants::QUEUE_BUTTON_FORMAT));
		$form->addButton("Unranked" . "\n" . str_replace(['{playing}', '{queued}'], [$duels->duelManager->getRunningTypes($kitTypes, QueueManager::UNRANKED), $duels->queueManager->getInQueueTypes($kitTypes, QueueManager::UNRANKED)], Constants::QUEUE_BUTTON_FORMAT));
        $form->addButton("Bot Duels"); //TODO Add function(s) to BotDuels to get {playing} and {queued}

		if ($duels->queueManager->getQueuePlayerIn($player) !== null) {
            $form->addButton("Leave Queue", Constants::BACK_TYPE, Constants::BACK_IMAGE);
        }

		$player->sendForm($form);

	}

	/**
	 * @param Player $player
	 * @param int $ranked
	 */
	public function queueTypeMenu(Player $player, ?int $ranked): void
	{
		/** @var Duels $duels */
		$duels = Duels::getInstance();

		if ($ranked === 3) {
			$duels->queueManager->removePlayerFromQueue($player);
			return;
		}

		$kits = Kits::getInstance();
        $askedForDuel = $this->command->getAskedForDuel();
		$kitTypes = $kits->kitTypes;

		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data) use ($duels, $kitTypes, $ranked, $askedForDuel): void {
			if (isset($data)) {
				if (!isset(array_values($kitTypes)[$data])) {
                    $this->rankMenu($player);
                } else {
					$kitType = array_values($kitTypes)[$data];
					if (implode(':', [$ranked, $kitType]) !== $duels->queueManager->getQueuePlayerIn($player)) {
                        $duels->queueManager->removePlayerFromQueue($player);
                    }
					if ($duels->queueManager->queuePlayer($player, $kitType, $ranked)) {
						$player->sendMessage(TextFormat::GOLD . "You joined a queue for " . $duels->queueManager->translateQueue($ranked) . " $kitType");
						$duels->queueManager->checkIfQueue($kitType, $ranked);
                        if (in_array($player, $askedForDuel, true)) {
                            $key = array_search($player, $askedForDuel, true);
                            $explosion = explode(':', $key);
                            $player = $duels->getServer()->getPlayerExact($explosion[0]);
                            if($player !== null) {
                                unset($askedForDuel[$key]);
                            }
                        }
					}
				}
			}
		};

		$form = new SimpleForm($callable);

		$form->setTitle("Select a type!");

		foreach ($kitTypes as $kitType) {
			$lKitType = strtolower($kitType);
			/** @var Kit $kitValues */
			$kitValues = $kits->kits[$lKitType];
			$form->addButton($kitType . "\n" . str_replace(['{playing}', '{queued}'], [(string)$duels->duelManager->getRunningType($kitType, $ranked), (string)$duels->queueManager->getInQueueFor($kitType, $ranked)], Constants::QUEUE_BUTTON_FORMAT), $kitValues->getImageType(), $kitValues->getURL());
		}

		$form->addButton(Constants::BACK, Constants::BACK_TYPE, Constants::BACK_IMAGE);

		$player->sendForm($form);

	}

}