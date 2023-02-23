<?php
declare(strict_types=1);

namespace Versai\Duels\Queue\Task;

use pocketmine\scheduler\Task;
use Versai\Duels\Duels;
use Versai\Duels\Queue\QueueManager;
use Duo\vpractice\PracticeStats;
use function array_rand;
use function array_values;

class QueueTick extends Task {

	/** @var Duels $duels */
	private Duels $duels;
	/** @var string $queueKey */
	private string $queueKey;
	/** @var int[] */
	private array $eloArray;

	/**
	 * QueueTick constructor.
	 * @param Duels $duels
	 * @param string $queueKey
	 */
	public function __construct(Duels $duels, string $queueKey) {
		$this->duels = $duels;
		$this->queueKey = $queueKey;
	}

	public function onRun(): void {
		$queue = $this->duels->queueManager->getQueueFor($this->queueKey);
		$bang = explode(':', $this->queueKey);
		$kitType = $bang[1];

		if(count($queue) >= 2) {
			$end = end($queue);

			foreach ($queue as $queuedPlayer) {
				$name = $queuedPlayer->getName();
				$session = PracticeStats::getInstance()->getPlayerManager()->getSession($queuedPlayer);
				$elo = $session->getElo($kitType);
                $this->eloArray[$name] = $elo;
                if($end === $queuedPlayer) {
                    $randomPlayerName = array_rand($this->eloArray);
                    $closestPlayerName = $this->getClosestPlayer($randomPlayerName, $this->eloArray);
                    $server = $this->duels->getServer();
                    $randomPlayer = $server->getPlayerExact($randomPlayerName);

                    if($closestPlayerName !== null) {
                        $closestPlayer = $server->getPlayerExact($closestPlayerName);
                    } else {
                        $av = array_values($queue);
                        $randomPlayer = $av[0];
                        $closestPlayer = $av[1];
                    }

                    if(isset($randomPlayer) && isset($closestPlayer)) {
                        $this->duels->duelManager->createMatch([$randomPlayer, $closestPlayer], $kitType, 'Random', QueueManager::RANKED);
                        $this->duels->queueManager->removePlayerFromQueue($randomPlayer, false);
                        $this->duels->queueManager->removePlayerFromQueue($closestPlayer, false);
                    }
                }
			}
		} else {
            $this->getHandler()->remove();
        }
	}

	/**
	 * @param string $search
	 * @param array $arr
	 * @return null|string
	 */
	public function getClosestPlayer(string $search, array $arr): ?string {
		$closest = null;

	//	var_dump($search);
	//	var_dump($arr);

		foreach ($arr as $key => $item) {
			$abs = abs($item - $arr[$search]);
			if ((int) abs((int) $arr[$search] - (int) $closest) > (int) $abs && $search !== $key) {
                $closest = $key;
            }
		}
		return $closest;
	}

}