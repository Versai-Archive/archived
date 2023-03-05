<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Queue;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

use ARTulloss\Duels\Duels;
use ARTulloss\Duels\Manager;
use ARTulloss\Duels\Queue\Task\QueueTick;

/**
 * Class Queue
 * @package ARTulloss\Duels\Queue
 */
class QueueManager implements Manager{
	public const RANKED = 0;
	public const UNRANKED = 1;

	/** @var Duels $duels */
	private $duels;
	/** @var array */
	private $queue;

	/**
	 * Queue constructor.
	 * @param Duels $duels
	 */
	public function __construct(Duels $duels)
	{
		$this->duels = $duels;
	}

	/**
	 * @param Player $player
	 * @param string $kitType
	 * @param int $ranked
	 * @return bool
	 */
	public function queuePlayer(Player $player, string $kitType, int $ranked): bool
	{
		$queueKey = implode(':', [(int)$ranked, $kitType]);

		$playerName = $player->getName();

		if (isset($this->queue[$queueKey][$playerName])) {
			$player->sendMessage(TextFormat::RED . "You are already in that queue!");
			return false;
		}

		if ($this->duels->duelManager->getSpectatorsMatch($player) !== null) {
			$player->sendMessage(TextFormat::RED . 'You are already in a duel!');
			return false;
		}

		if($this->duels->duelManager->getPlayersMatch($player) !== null) {
			$player->sendMessage(TextFormat::RED . 'You are already in a match!');
			return false;
		}

		$this->queue[$queueKey][$playerName] = $player;

		return true;
	}

	/**
	 * @param string $queueKey
	 * @return array
	 */
	public function getQueueFor(string $queueKey): array {
		return $this->queue[$queueKey];
	}

	/**'
	 * @param $queueKey
	 * @param array $queue
	 */
	public function setQueue($queueKey, array $queue): void
	{
		$this->queue[$queueKey] = $queue;
	}

	/**
	 * @param string $kitType
	 * @param int $ranked
	 */
	public function checkIfQueue(string $kitType, int $ranked): void
	{
		$queueKey = implode(':', [$ranked, $kitType]);

		if (count($this->queue[$queueKey]) === 2) {
			if($ranked === QueueManager::RANKED) {
				$this->duels->getScheduler()->scheduleDelayedRepeatingTask(new QueueTick($this->duels, $queueKey), $this->duels->duelConfig['Settings']['Ranked-Queue-Time'] * 20, 20);
			} elseif($ranked === QueueManager::UNRANKED) {
				$this->duels->duelManager->createMatch($this->queue[$queueKey], $kitType, 'Random', QueueManager::UNRANKED);
				foreach ($this->queue[$queueKey] as $player)
					$this->removePlayerFromQueue($player, false);
			}
		}
	}

	/**
	 * This is used for removing a player from any queues they might be in, call it every time the
	 * player gets put in a queue to ensure there will only be one for performance
	 *
	 * Relies on there only being one person in queue!
	 *
	 * @param Player $player
	 * @param bool $message
	 */
	public function removePlayerFromQueue(Player $player, bool $message = true): void
	{
		if (is_array($this->queue))
			foreach ($this->queue as $queue => $playersInQueue)
				if (in_array($player, $playersInQueue, true)) {
					$bang = explode(':', $queue);
					$type = $this->translateQueue((int)$bang[0]); // (int)ranked:kitType
					if($message)
						$player->sendMessage(TextFormat::GOLD . "You left the queue for $type $bang[1]");
					unset($this->queue[$queue][$player->getName()]);
					return; // Exit once the array key is unset
				}
	}

	/**
	 * @param int $ranked
	 * @param bool $caps
	 * @return string
	 */
	public function translateQueue(int $ranked, bool $caps = false): string
	{
		$returned = 'Error';

		if ($ranked === 0)
			$returned = 'ranked';
		if ($ranked === 1)
			$returned = 'unranked';

		return $caps ? ucwords($returned) : $returned;
	}

	/**
	 * @param Player $player
	 * @return null|string
	 */
	public function getQueuePlayerIn(Player $player): ?string
	{
		if (is_array($this->queue))
			foreach ($this->queue as $queue => $playersInQueue)
				if (in_array($player, $playersInQueue, true))
					return $queue;
		return null;
	}

	/**
	 * @param string $kitType
	 * @param int $ranked
	 * @return int
	 */
	public function getInQueueFor(string $kitType, int $ranked): int
	{
		if (isset($this->queue[implode(':', [$ranked, $kitType])]))
			return count($this->queue[implode(':', [$ranked, $kitType])]);
		return 0;
	}


	/**
	 * Just like the other in the DuelManager
	 * @param array $kitTypes
	 * @param int $ranked
	 * @return int
	 */
	public function getInQueueTypes(array $kitTypes, int $ranked): int
	{
		$totalQueueType = 0;

		foreach ($kitTypes as $kitType)
			$totalQueueType += $this->getInQueueFor($kitType, $ranked);

		return $totalQueueType;
	}

}