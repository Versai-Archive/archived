<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Match\Task;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use ARTulloss\Arenas\Arena;
use ARTulloss\Duels\Duels;
use ARTulloss\Duels\Level\DuelLevel;
use ARTulloss\Duels\Match\DuelManager;
use ARTulloss\Duels\Party\Party;
use ARTulloss\Duels\Utilities\Utilities;

use function array_keys;
use function array_values;

/**
 * Class Party
 * @package ARTulloss\Duels\Match\Task
 */
class PartyHeartbeat extends Heartbeat
{
	protected const END_MESSAGE = TextFormat::BLUE . '{winner} won a match against {players} players';
	protected const DUEL_LENGTH_KEY = 'Party-Duel-Length';

	/** @var Party $party */
	private $party;

	/**
	 * PartyHeartbeat constructor.
	 * @param Duels $duels
	 * @param Party $party
	 * @param DuelManager $manager
	 * @param DuelLevel $level
	 * @param array $players
	 * @param string $kitType
	 * @param string $levelName
	 * @param Arena $arena
	 */
	public function __construct(Duels $duels, Party $party, DuelManager $manager, DuelLevel $level, array $players, string $kitType, string $levelName, Arena $arena)
	{
		parent::__construct($duels, $manager, $level, $players, $kitType, $levelName, $arena);
		$this->party = $party;
	}

	protected function sendPlayersScoreboard(): void
	{
		$players = array_values($this->players);

		$playersRemaining = count($this->players);
		$playersTotal = count((array)$this->removedPlayers) + $playersRemaining;

		/**
		 * @var int $index
		 * @var Player $player
		 */
		foreach ($players as $index => $player)
			$this->scoreboardTask->setTextForByName($player->getName(), (array) str_replace(['{kit}', '{combat}', '{time}', '{rtime}', '{ping}', '{remaining}', '{total}', '{spectators}'], [$this->kitType, $this->duels->cooldown->combat->getCooldown($players[0]), $this->countdown, Utilities::secondsToReadableTime($this->countdown), $player->getPing(), $playersRemaining, $playersTotal, count((array)$this->getSpectators())], $this->duels->scoreboardArray['Party']));
	}

	public function sendPlayersBossbar(): void
	{
		foreach (array_keys($this->players) as $name) {
			$this->bossbarTask->setTextForByName($name, (array) str_replace(['{time}', '{rtime}'], [$this->countdown, Utilities::secondsToReadableTime($this->countdown)], $this->duels->bossbarArray['Party']), true);
			$this->bossbarTask->setHealthProgressByName($name, $this->countdown, $this->duels->duelConfig['Settings'][static::DUEL_LENGTH_KEY]);
		}
	}

	public function sendEndMatchScoreboard(): void
	{
		foreach ($this->players as $player)
			$this->scoreboardTask->setTextForByName($player->getName(), str_replace(['{kit}', '{players}'], [$this->kitType, count($this->removedPlayers), count((array)$this->getSpectators())], $this->duels->scoreboardArray['Party-Win']));
	}

	public function sendEndMatchBossbar(): void
	{
		foreach ($this->players as $player) {
			$this->bossbarTask->setTextForByName($player->getName(), (array) str_replace(['{time}', '{rtime}'], [$this->countdown, Utilities::secondsToReadableTime($this->countdown)], $this->duels->bossbarArray['Party-Win']), true);
			$this->bossbarTask->setHealthProgressByName($player->getName(), $this->matchEndTimer, $this->duels->duelConfig['Settings']['End-Game-Time']);
		}
	}

	public function sendSpectatorsScoreboard(): void
	{
		$playersRemaining = count($this->players);
		$playersTotal = count((array)$this->removedPlayers) + $playersRemaining;
		foreach ($this->spectatingPlayers as $player)
			$this->scoreboardTask->setTextForByName($player->getName(), str_replace(['{time}', '{rtime}', '{spectators}', '{kit}', '{remaining}', '{total}'], [$this->countdown, Utilities::secondsToReadableTime($this->countdown), count($this->spectatingPlayers), $this->kitType, $playersRemaining, $playersTotal], $this->duels->scoreboardArray['Party-Spectate']));
	}

	public function sendSpectatorsBossbar(): void
	{
		foreach ($this->spectatingPlayers as $player) {
			$this->bossbarTask->setTextForByName($player->getName(), (array) str_replace(['{time}', '{rtime}'], [$this->countdown, Utilities::secondsToReadableTime($this->countdown)], $this->duels->bossbarArray['Party-Spectate']), true);
			$this->bossbarTask->setHealthProgressByName($player->getName(), $this->countdown, $this->duels->duelConfig['Settings'][static::DUEL_LENGTH_KEY]);
		}
	}

    /**
     * @param Server $server
     * @param Player $player
     */
    public function broadcastEndGame(Server $server, Player $player): void
	{
		$players = count($this->removedPlayers);
        $server->broadcastMessage(str_replace(['{winner}', '{players}'], [$player->getDisplayName(), $players], self::END_MESSAGE));
    }

	/**
	 * @return Party
	 */
	public function getParty(): Party
	{
		return $this->party;
	}

}