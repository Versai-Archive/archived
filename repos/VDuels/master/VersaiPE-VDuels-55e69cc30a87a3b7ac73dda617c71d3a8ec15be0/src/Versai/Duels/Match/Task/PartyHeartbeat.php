<?php
declare(strict_types=1);

namespace Versai\Duels\Match\Task;

use Ifera\ScoreHud\libs\JackMD\ScoreFactory\ScoreFactory;
use Ifera\ScoreHud\scoreboard\Scoreboard;
use Ifera\ScoreHud\session\PlayerManager;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Versai\arenas\Arena;
use Versai\combat\CombatLogger;
use Versai\Duels\Duels;
use Versai\Duels\Level\DuelLevel;
use Versai\Duels\Match\DuelManager;
use Versai\Duels\Party\Party;
use Versai\Duels\Utilities\Utilities;
use function array_values;

class PartyHeartbeat extends Heartbeat {

	protected const END_MESSAGE = TextFormat::BLUE . '{winner} won a match against {players} players';
	protected const DUEL_LENGTH_KEY = 'Party-Duel-Length';

	/** @var Party $party */
	private Party $party;

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
	public function __construct(Duels $duels, Party $party, DuelManager $manager, DuelLevel $level, array $players, string $kitType, string $levelName, Arena $arena) {
		parent::__construct($duels, $manager, $level, $players, $kitType, $levelName, $arena);
		$this->party = $party;
	}

	protected function sendPlayersScoreboard(): void {
        $players = array_values($this->players);

        $playersRemaining = count($this->players);
        $playersTotal = count((array)$this->removedPlayers) + $playersRemaining;

        /**
         * @var int $index
         * @var Player $player
         */
        foreach ($players as $index => $player) {
            $combatTime = CombatLogger::getInstance()->getCombatManager()->getSession($player)->getTaggedTime();
            $text = (array)str_replace([
                '{kit}',
                '{combat}',
                '{time}',
                '{rtime}',
                '{ping}',
                '{remaining}',
                '{total}',
                '{spectators}'
            ], [
                $this->kitType,
                $combatTime,
                $this->countdown,
                Utilities::secondsToReadableTime($this->countdown),
                $player->getNetworkSession()->getPing(),
                $playersRemaining,
                $playersTotal,
                count((array)$this->getSpectators())
            ], $this->duels->scoreboardArray['Party']);

            ScoreFactory::setScore($player, "§l§bVersai Practice");
            $pm = PlayerManager::get($player);
            $scoreboard = new Scoreboard($pm, $text);
            $scoreboard->update()->display();
            $pm->setScoreboard($scoreboard);
        }
    }

	public function sendEndMatchScoreboard(): void {
        foreach ($this->players as $player) {
            $text = $this->duels->scoreboardArray['Party-Win'];

            ScoreFactory::setScore($player, "§l§bVersai Practice");
            $pm = PlayerManager::get($player);
            $scoreboard = new Scoreboard($pm, $text);
            $scoreboard->update()->display();
            $pm->setScoreboard($scoreboard);
        }
    }

	public function sendSpectatorsScoreboard(): void {
		$playersRemaining = count($this->players);
		$playersTotal = count((array)$this->removedPlayers) + $playersRemaining;
		foreach ($this->spectatingPlayers as $player) {
            $text = (array)str_replace([
                '{time}',
                '{spectators}',
                '{remaining}',
                '{total}'
            ], [
                Utilities::secondsToReadableTime($this->countdown),
                count($this->spectatingPlayers),
                $playersRemaining,
                $playersTotal
            ], $this->duels->scoreboardArray['Party-Spectate']);

            ScoreFactory::setScore($player, "§l§bVersai Practice");
            $pm = PlayerManager::get($player);
            $scoreboard = new Scoreboard($pm, $text);
            $scoreboard->update()->display();
            $pm->setScoreboard($scoreboard);
        }
	}

    /**
     * @param Server $server
     * @param Player $player
     */
    public function broadcastEndGame(Server $server, Player $player): void {
		$players = count($this->removedPlayers);
        $server->broadcastMessage(str_replace(['{winner}', '{players}'], [$player->getDisplayName(), $players], self::END_MESSAGE));
    }

	/**
	 * @return Party
	 */
	public function getParty(): Party {
		return $this->party;
	}

}