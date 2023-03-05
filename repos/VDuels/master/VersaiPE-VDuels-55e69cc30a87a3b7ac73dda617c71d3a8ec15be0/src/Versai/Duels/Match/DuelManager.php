<?php
declare(strict_types=1);

namespace Versai\Duels\Match;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use Versai\arenas\Arena;
use Versai\arenas\Arenas;
use Versai\Duels\Duels;
use Versai\Duels\Level\DuelLevel;
use Versai\Duels\Manager;
use Versai\Duels\Match\Task\Heartbeat;
use Versai\Duels\Match\Task\PartyHeartbeat;
use Versai\Duels\Match\Task\RankedDuelHeartbeat;
use Versai\Duels\Match\Task\UnrankedDuelHeartbeat;
use Versai\Duels\Party\Party;
use Versai\Duels\Queue\QueueManager;
use Duo\kits\Kits;
use function explode;
use function in_array;
use function shuffle;
use function strtolower;


class DuelManager implements Manager {

	/** @var array $runningMatches */
	private array $runningMatches = [];

    /**
     * @param array $players
     * @param string $kitType
     * @param string $map
     * @param int $ranked
     * @param Party|null $party
     */
	public function createMatch(array $players, string $kitType, string $map = 'Random', int $ranked = QueueManager::UNRANKED, Party $party = null): void {
		// Ensure the players are online

		foreach ($players as $key => $player) {
            if ($player->isOnline()) {
                unset($players[$key]);
                $players[$player->getName()] = $player;
            }
        }

		$playerCount = count($players);

		// Block out 1 person party duels lol

		if($playerCount === 1) {
			array_values($players)[0]->sendMessage(TextFormat::RED . 'You have to have at least 2 people to do a duel!');
			return;
		}

		/** @var Duels $duels */
		$duels = Duels::getInstance();

        $duelLevels = $duels->levels;

		$allKitIDs = Kits::getInstance()->kitIDs;

		if($map === 'Random') {
            shuffle($duelLevels);
            foreach ($duelLevels as $duelLevel) {

                // Kit IDs
                if (isset($allKitIDs[strtolower($kitType)])) {
                    $kitID = $allKitIDs[strtolower($kitType)];
                    if (!in_array($kitID, $duelLevel->getIDs(), true)) { // Ensure the kit id matches with that of the arena!
                        unset($duelLevel);
                        continue;
                    }
                    break;
                }
            }
        } else {
            $duelLevel = $duelLevels[$map];
        }
        $kitID = $allKitIDs[strtolower($kitType)];

		if(!isset($duelLevel)) {
			$duels->getServer()->getLogger()->critical('No map for kit type ' . $kitType);
			foreach ($players as $player) {
                $player->sendMessage(TextFormat::RED . 'No maps work for this kit type, please contact an administrator!');
            }
			Kits::getInstance()->unregisterKit($kitType);
			return;
		}

		$newLevelName = uniqid($duelLevel->getLevel()->getDisplayName() . '-');

		foreach ($players as $player) {
            $player->sendMessage(TextFormat::GREEN . 'Match starting soon, generating a new arena!');
        }
		$callback = function () use ($duels, $duelLevel, $players, $kitType, $kitID, $newLevelName, $ranked, $playerCount, $party): void {
			$server = $duels->getServer();

			// Register the level
			$duels->levelManager->registerTempLevel($newLevelName, $duels->levelManager->getPath() . $newLevelName);

			// Load the level
			$server->getWorldManager()->loadWorld($newLevelName);

			// Midpoint
			$x = 0;
			$y = 0;
			$z = 0;
			$i = 0;

            $positions = [];
            foreach($duelLevel->getPositions() as $sPos){
                $ePos = explode(":", $sPos);
                $positions[] = new Position((int)$ePos[0], (int)$ePos[1], (int)$ePos[2], null);
            }
			foreach ($positions as $position) {
				$x += $position->getX();
				$y += $position->getY();
				$z += $position->getZ();
				$i++;
			}
			$midpoint = new Position($x / $i, $y / $i, $z / $i, null);

			// New arena with only the ID of the kit it's supposed to have
			$arena = new Arena($newLevelName, [$kitID], $midpoint, $duels->kitSettings[$kitType]);
			Arenas::getInstance()->registerArena($arena); // FUCKING REGISTER FFS

			$tempLevel = $duels->getServer()->getWorldManager()->getWorldByName($newLevelName);

			if($tempLevel === null) {
			    foreach ($players as $player) {
			        $player->sendMessage(TextFormat::RED . 'Something went wrong... failed to start match!');
                }
			    return;
            }

			$this->teleportPlayers($duelLevel, $tempLevel, $players);

            $this->startMatch($arena, $duelLevel, $newLevelName, $playerCount, $ranked, $kitType, $players, $party);

		};

		$duels->levelManager->copyLevel($duelLevel->getLevel(), $newLevelName, $callback);

	}

	/**
	 * @param Arena $arena
	 * @param DuelLevel $level
	 * @param string $newLevelName
	 * @param int $playerCount
	 * @param int $ranked
	 * @param string $kitType
	 * @param array $players
	 * @param Party|null $party
	 * @return bool
	 */
	public function startMatch(Arena $arena, DuelLevel $level, string $newLevelName, int $playerCount, int $ranked, string $kitType, array $players, Party $party = null): bool{
		$duels = Duels::getInstance();

		$key = implode(':', [$ranked, $kitType]);

		if($playerCount === 2) {
            if ($ranked === QueueManager::RANKED) {
                $this->runningMatches[$key][$newLevelName] = new RankedDuelHeartbeat($duels, $this, $level, $players, $kitType, $newLevelName, $arena);
            } else {
                $this->runningMatches[$key][$newLevelName] = new UnrankedDuelHeartbeat($duels, $this, $level, $players, $kitType, $newLevelName, $arena);
            }
        }
		if($playerCount > 2) {
			if($party !== null) {
                $this->runningMatches[$key][$newLevelName] = new PartyHeartbeat($duels, $party, $this, $level, $players, $kitType, $newLevelName, $arena);
            } else {
				$duels->getServer()->getLogger()->error('Party is null in a match with more than 2 players'); # TODO Add more game types and remove this ?
				return false;
			}
		}
		$duels->getScheduler()->scheduleRepeatingTask($this->runningMatches[$key][$newLevelName], 20);
		return true;
	}

	/**
	 * @param DuelLevel $duelLevel
	 * @param World $tempLevel
	 * @param array $players
	 */
	public function teleportPlayers(DuelLevel $duelLevel, World $tempLevel, array $players): void {
		$posCount = count($duelLevel->getPositions());

		$times = 0;

		foreach (array_values($players) as $index => $player) {
			if ($player instanceof Player && $player->getPosition()->isValid()) {

			    $positions = [];
			    foreach($duelLevel->getPositions() as $position){
			        $ePos = explode(":", $position);
			        if(!$tempLevel->isLoaded()) return;
			        $positions[] = new Position((int)$ePos[0], (int)$ePos[1], (int)$ePos[2], $tempLevel);
                }

				// Should work- untested
				$key = $index - ($posCount * $times);
				$pos = $positions[$key];

				if($key + 1 === $posCount) {
                    $times++;
                }

				$player->teleport($pos);
			}
		}
	}

	/**
	 * @return int
	 */
	public function getTotalMatchesRunning(): int {
		$i = 0;
		foreach ($this->runningMatches as $matchesOfTypes) {
            $i += count($matchesOfTypes);
        }
		return $i;
	}

	/**
	 * @param array $kitTypes - The kit types you want to count for :P can be all or some
	 * @param int $ranked
	 * @return int
	 */
	public function getRunningTypes(array $kitTypes, int $ranked): int {
		$totalRunningType = 0;

		foreach ($kitTypes as $kitType) {
            $totalRunningType += $this->getRunningType($kitType, $ranked);
        }

		return $totalRunningType;
	}

	/**
	 * @param string $kitType
	 * @param int $ranked
	 * @return int
	 */
	public function getRunningType(string $kitType, int $ranked): int {
		$key = implode(':', [(int)$ranked, $kitType]);
		if (isset($this->runningMatches[$key]) && is_array($this->runningMatches[$key])) {
            return count($this->runningMatches[$key]);
        } else {
            return 0;
        }
	}

	/**
	 * @param Heartbeat $heartbeat
	 */
	public function removeMatch(Heartbeat $heartbeat): void {
		$duels = Duels::getInstance();
		$levelName = $heartbeat->getTempLevelName();
		Arenas::getInstance()->unregisterArena($heartbeat->getArena());

		$ranked = $heartbeat instanceof RankedDuelHeartbeat ? QueueManager::RANKED : QueueManager::UNRANKED;

		unset($this->runningMatches[$key = implode(':', [(int)$ranked, $heartbeat->getKitType()])][$heartbeat->getTempLevelName()]);

		$server = $duels->getServer();
		$level = $server->getWorldManager()->getWorldByName($levelName);
		if($level !== null) {
            $server->getWorldManager()->unloadWorld($level);
        }
		$duels->levelManager->deleteTempLevel($levelName);
	}

	/**
	 * @param Player $player
	 * @return Heartbeat|null
	 */
	public function getPlayersMatch(Player $player): ?Heartbeat {
		if (isset($this->runningMatches)) {
			foreach ($this->runningMatches as $matchTypes) {
                foreach ($matchTypes as $match) {
                    if ($match->isInMatch($player)) {
                        return $match;
                    }
                }
            }
		}
		return null;
	}

	/**
	 * @param Player $player
	 * @return Heartbeat|null
	 */
	public function getSpectatorsMatch(Player $player): ?Heartbeat {
		$name = $player->getName();
		foreach ($this->getAllRunningMatchHeartbeats() as $heartbeat) {
            if (isset($heartbeat->getSpectators()[$name])) {
                return $heartbeat;
            }
        }
		return null;
	}

	/**
	 * @return Heartbeat[]
	 */
	public function getAllRunningMatchHeartbeats(): ?array {
		$heartbeats = [];
		foreach ((array)$this->runningMatches as $match) {
            foreach ($match as $heartbeat) {
                if ($heartbeat instanceof Heartbeat) {
                    $heartbeats[] = $heartbeat;
                }
            }
        }
		return $heartbeats;
	}

}