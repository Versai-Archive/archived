<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/12/2019
 * Time: 8:23 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub\Duel;

use ARTulloss\Kits\Kit;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use jojoe77777\FormAPI\SimpleForm;

use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Queue\QueueManager;
use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Duels;

use function array_values;
use function strtolower;
use function str_replace;
use function implode;

/**
 * Class DuelQueue
 * @package ARTulloss\Duels\Commands\Sub\Duel
 */
class DuelQueue extends SubCommand
{
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
			if (isset($data))
				$this->queueTypeMenu($player, $data);
		};

		$form = new SimpleForm($callable);

		$form->setTitle("Choose a Duel Type!");

		/** @var Duels $duels */
		$duels = $this->command->getPlugin();

		$kitTypes = $this->command->getKits()->kitTypes;

		$form->addButton("Unranked" . "\n" . str_replace(['{playing}', '{queued}'], [$duels->duelManager->getRunningTypes($kitTypes, QueueManager::UNRANKED), $duels->queueManager->getInQueueTypes($kitTypes, QueueManager::UNRANKED)], Constants::QUEUE_BUTTON_FORMAT));
		$form->addButton("Ranked" . "\n" . str_replace(['{playing}', '{queued}'], [$duels->duelManager->getRunningTypes($kitTypes, QueueManager::RANKED), $duels->queueManager->getInQueueTypes($kitTypes, QueueManager::RANKED)], Constants::QUEUE_BUTTON_FORMAT));

		if ($duels->queueManager->getQueuePlayerIn($player) !== null)
			$form->addButton("Leave Queue", Constants::BACK_TYPE, Constants::BACK_IMAGE);

		$player->sendForm($form);

	}

	/**
	 * @param Player $player
	 * @param int $ranked
	 */
	public function queueTypeMenu(Player $player, int $ranked): void
	{

		/** @var Duels $duels */
		$duels = $this->command->getPlugin();

		if ($ranked === 2) {
			$duels->queueManager->removePlayerFromQueue($player);
			return;
		}

		$kits = $this->command->getKits();

		$kitTypes = $kits->kitTypes;

		/**
		 * @param Player $player
		 * @param $data
		 */
		$callable = function (Player $player, $data) use ($duels, $kitTypes, $ranked): void {
			if (isset($data)) {
				if (!isset(array_values($kitTypes)[$data]))
					$this->rankMenu($player);
				else {
					$kitType = array_values($kitTypes)[$data];
					if (implode(':', [$ranked, $kitType]) !== $duels->queueManager->getQueuePlayerIn($player))
						$duels->queueManager->removePlayerFromQueue($player);
					if ($duels->queueManager->queuePlayer($player, $kitType, $ranked)) {
						$player->sendMessage(TextFormat::GOLD . "You joined a queue for " . $duels->queueManager->translateQueue($ranked) . " $kitType");
						$duels->queueManager->checkIfQueue($kitType, $ranked);
					}
				}
			}
		};

		$form = new SimpleForm($callable);

		$form->setTitle("Select a type!");

		foreach ($kitTypes as $kitType) {
			$lKitType = strtolower($kitType);
			/** @var Kit[] $kitValues */
			$kitValues = array_values($kits->kits[$lKitType]);
			$form->addButton($kitType . "\n" . str_replace(['{playing}', '{queued}'], [(string)$duels->duelManager->getRunningType($kitType, $ranked), (string)$duels->queueManager->getInQueueFor($kitType, $ranked)], Constants::QUEUE_BUTTON_FORMAT), $kitValues[0]->getImageType(), $kitValues[0]->getURL());
		}

		$form->addButton(Constants::BACK, Constants::BACK_TYPE, Constants::BACK_IMAGE);

		$player->sendForm($form);

	}

}