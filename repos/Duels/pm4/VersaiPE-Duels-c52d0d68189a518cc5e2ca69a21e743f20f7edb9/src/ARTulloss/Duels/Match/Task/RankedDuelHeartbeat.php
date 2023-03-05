<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/23/2019
 * Time: 3:50 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Match\Task;

use ARTulloss\Duels\Elo\Elo;
use ARTulloss\Duels\Elo\Rating;
use pocketmine\utils\TextFormat;

use ARTulloss\Duels\Queue\QueueManager;

/**
 * Class RankedDuelHeartbeat
 * @package ARTulloss\Duels\Match\Task
 */
class RankedDuelHeartbeat extends UnrankedDuelHeartbeat
{
	protected const TYPE = 'Ranked';
	protected const END_MESSAGE = TextFormat::GOLD . '{winner} won a ranked match against {loser} with type {type}!';

	/**
	 * @param string $loserName
	 * @return bool
	 */
	public function removeFromPlayers(string $loserName): bool
	{
		if(!isset($this->players[$loserName])) {
			$this->duels->getLogger()->error("There was a glitch! - The players names must be the keys!");
			return false;
		}

		if(count($this->players) === 2) {

			if ($this->stage !== Heartbeat::STAGE_FINISHED) {
				$this->stage = Heartbeat::STAGE_FINISHED;
				$this->matchEndTimer = $this->duels->duelConfig['Settings']['End-Game-Time'];
			}

			$this->handleElo($loserName);

		}

		$this->scoreboardTask->resetPlayersTextByName($loserName); // Reset their scoreboard
		$this->bossbarTask->resetPlayersTextByName($loserName);
		$this->bossbarTask->resetBarProgressByName($loserName);
		$this->removedPlayers[$loserName] = $this->players[$loserName];
		unset($this->players[$loserName]);
		return true;
	}

	/**
	 * Default to unranked
	 *
	 * @return int
	 */
	public function getRanked(): int{
		return QueueManager::RANKED;
	}

	/**
	 * @param string $loserName
	 */
	public function handleElo(string $loserName): void{
        $players = $this->getPlayers();
        $this->duels->eloManager->selectElo($this->getKitType(), $loserName, function ($value) use ($players, $loserName): void{

            $loserElo = isset($value[0]) ? $value[0]['elo'] : Elo::DEFAULT_ELO;

            foreach ($players as $player)
                if($players[$loserName] !== $player)
                    $winner = $player;

            if(isset($winner)) {
                $winnerName = $winner->getName();
                $this->duels->eloManager->selectElo($this->getKitType(), $winnerName, function ($value) use ($players, $winner, $winnerName, $loserName, $loserElo): void{
                    $winnerElo = isset($value[0]) ? $value[0]['elo'] : Elo::DEFAULT_ELO;
                    $rating = new Rating($winnerElo, $loserElo, Rating::WIN, Rating::LOSS);
                    $ratings = $rating->calculate();
                    $this->duels->eloManager->setElo($this->getKitType(), $winnerName, $ratings['a'], null);
                    $winner->addTitle('+' . strval($ratings['a'] - $winnerElo), '   Win', 5, 10, 5);
                    $this->duels->eloManager->setElo($this->getKitType(), $loserName, $ratings['b'], null);
                    $players[$loserName]->addTitle('-' . strval($loserElo - $ratings['b']), '  Loss', 5, 10, 5);
                });
            } else
                $this->duels->getLogger()->error("[Elo] There was a glitch! - Winner not found");
        });
	}

}