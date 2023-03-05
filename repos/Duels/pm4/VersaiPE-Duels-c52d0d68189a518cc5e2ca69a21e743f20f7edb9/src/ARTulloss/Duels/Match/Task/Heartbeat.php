<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/28/2018
 * Time: 5:24 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Match\Task;

use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\ClickSound;
use function array_keys;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ARTulloss\Arenas\Arena;
use ARTulloss\Duels\Duels;
use ARTulloss\Duels\Level\DuelLevel;
use ARTulloss\Duels\Match\DuelManager;
use ARTulloss\Groups\Groups;
use ARTulloss\Groups\Task\ScoreboardTask;
use ARTulloss\Duels\Queue\QueueManager;
use ARTulloss\Duels\Events\MatchStartEvent;
use ARTulloss\Duels\Events\PlayerDuelEvent;
use ARTulloss\Duels\Events\PlayerLoseEvent;
use ARTulloss\Groups\Task\BossbarTask;
use ReflectionException;
use function str_replace;
use function array_merge;
use function array_values;

/**
 * Class Heartbeat
 * @package ARTulloss\Duels\Task
 */
class Heartbeat extends Task
{
	protected const END_MESSAGE = TextFormat::GOLD . '{winner} won a duel!'; # Generic message :P

	public const STAGE_COUNTDOWN = 1;
	public const STAGE_PLAYING = 2;
	public const STAGE_FINISHED = 3;

	protected const DUEL_LENGTH_KEY = 'Generic';

	/** @var Duels $duels */
	protected $duels;
	/** @var DuelManager $manager */
	protected $manager;
	/** @var int $countdown */
	protected $countdown;
	/** @var int $matchEndTimer */
	protected $matchEndTimer;
	/** @var DuelLevel $level */
	protected $level;
	/** @var Player[] */
	protected $players;
	/** @var Player[] */
	protected $removedPlayers;
	/** @var Player[] */
	protected $spectatingPlayers;
	/** @var int $stage */
	protected $stage = Heartbeat::STAGE_COUNTDOWN;
	/** @var string $kitType */
	protected $kitType;
	/** @var string $tempLevel */
	protected $tempLevel;
	/** @var ScoreboardTask $scoreboardTask */
	protected $scoreboardTask;
	/** @var BossbarTask $bossbarTask */
	protected $bossbarTask;
	/** @var Arena $arena */
	private $arena;
	/**
	 * Heartbeat constructor.
	 * @param Duels $duels
	 * @param DuelManager $manager
	 * @param DuelLevel $level
	 * @param array $players
	 * @param string $kitType
	 * @param string $levelName
	 * @param Arena $arena
	 */
	public function __construct(Duels $duels, DuelManager $manager, DuelLevel $level, array $players, string $kitType, string $levelName, Arena $arena)
	{
		$this->duels = $duels;
		$this->countdown = $this->duels->duelConfig['Settings'][static::DUEL_LENGTH_KEY];
		$this->manager = $manager;
		$this->level = $level;
		$this->players = $players;
		$this->kitType = $kitType;
		$this->tempLevel = $levelName;

		$groups = Groups::getInstance();

		# Scoreboard

		$this->scoreboardTask = $groups->scoreboardListener->getTask();

		# Bossbar

		$this->bossbarTask = $groups->bossbarListener->getTask();

		// Arena

		$this->arena = $arena;

		// Freeze players and reset

		$this->preparePlayers($players);
	}
	/**
	 * @param Player[] $players
	 */
	public function preparePlayers(array $players): void
	{
		foreach ($players as $player) {
			$player->setImmobile();
			$player->setGamemode(GameMode::SURVIVAL());
			$player->setHealth((float)$player->getMaxHealth());
			$player->getHungerManager()->setFood(20);
			$player->getHungerManager()->setSaturation(20);
			$player->getEffects()->clear();
		}
	}
	/**
	 * @param int $currentTick
	 * @throws ReflectionException
	 */
	public function onRun(): void
	{
		// Send the spectators their scoreboard

		if($this->spectatingPlayers !== null) {
			$this->sendSpectatorsScoreboard();
			$this->sendSpectatorsBossbar();
		}

		// End of the match

		if ($this->stage === Heartbeat::STAGE_FINISHED && is_array($this->removedPlayers)) {

			$this->sendEndMatchScoreboard();
			$this->sendEndMatchBossbar();

			if ($this->matchEndTimer === 0) {
			    $player = $this->getWinner();
                $this->stopMatch();
                $this->handleWinner($player);
            }
		} else {
			// Scoreboard and Bossbar
			$this->sendPlayersScoreboard();
			$this->sendPlayersBossbar();
		}
		$this->matchEndTimer--;

		$diff = max(array_keys($this->duels->countdownArray)) - abs($this->duels->duelConfig['Settings'][static::DUEL_LENGTH_KEY] - $this->countdown);

		//	var_dump($diff);

		if (isset($this->duels->countdownArray[$diff])) {

			//	var_dump($this->duels->countdownConfig[$diff]);

			// Countdown messages

			$bang = explode(':', $this->duels->countdownArray[$diff]);

			$levelName = $this->level->getName();

			$author = $this->level->getAuthor();

			if ($bang[0] !== '-')
				foreach ($this->players as $player)
					$player->sendMessage(str_replace(['{map}', '{author}', '{seconds}'], [$levelName, $author, $diff], $bang[0]));

			if ($bang[1] !== '-')
				foreach ($this->players as $player)
					$player->sendPopup(str_replace(['{map}', '{author}', '{seconds}'], [$levelName, $author, $diff], $bang[1]));

			if ($bang[2] !== '-')
				foreach ($this->players as $player)
					$player->addTitle(str_replace(['{map}', '{author}', '{seconds}'], [$levelName, $author, $diff], $bang[2]), '', 5, 10, 5);

			// Sound

			foreach ($this->players as $player) {
				$level = $this->duels->getServer()->getWorldManager()->getWorldByName($this->tempLevel);
				if ($level !== null)
					$level->addSound($player->getPosition() ,new ClickSound((float)$player->getLocation()->getPitch()));
			}
		}

		if ($diff === 0) {

			// Make sure no players left and stopped the match early before setting the stage
			if($this->stage === Heartbeat::STAGE_COUNTDOWN)
				$this->stage = Heartbeat::STAGE_PLAYING;
			else
				return;

			(new MatchStartEvent($this))->call();
			$this->givePlayersKits();
			foreach ($this->players as $player)
				$player->setImmobile(false);
		}

		if ($this->countdown === 0) {
			$this->stage = Heartbeat::STAGE_FINISHED;

			/** @var Player $player */
			foreach (array_merge((array)$this->players, (array)$this->spectatingPlayers) as $player) {
				if ($player !== null) {
					$this->resetAndRemovePlayer($player);
					$player->sendMessage(TextFormat::RED . "The match ended because it ran out of time!");
				}
			}

			$this->manager->removeMatch($this);
			$this->getHandler()->remove();
		} else
			$this->countdown--;
	}
	/**
	 * Send players the in match scoreboard
	 */
	protected function sendPlayersScoreboard(): void{}
    /**
     * Send the players their bossbar
     */
	protected function sendPlayersBossbar(): void{}
	/**
	 * Send the winning player(s) the end match scoreboard
	 */
	protected function sendEndMatchScoreboard(): void{}
    /**
     * Send the end match bossbar
     */
	protected function sendEndMatchBossbar(): void{}
	/**
	 * Send the spectators a scoreboard
	 */
	protected function sendSpectatorsScoreboard(): void{}
    /**
     * Send the spectators a bossbar
     */
	public function sendSpectatorsBossbar(): void{}
	/**
	 * @param Player $player
	 */
	public function resetAndRemovePlayer(Player $player): void{
		$player->removeAllEffects();
		$player->setHealth((float)$player->getMaxHealth());
		$name = $player->getName();
		$this->scoreboardTask->resetPlayersTextByName($name);
		$this->bossbarTask->resetPlayersTextByName($name);
		$this->bossbarTask->resetBarProgressByName($name);
		$player->teleport($this->duels->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
	}
    /**
     * @return null|Player
     */
    public function getWinner(): ?Player{
        $ak = array_keys($this->players);

        if(!isset($ak[0])) // Otherwise crash if player leaves
            return null;

        if($this->stage !== Heartbeat::STAGE_FINISHED)
            return null;

        return array_values($this->players)[0];
    }

    /**
     * @param Player|null $player
     * @throws ReflectionException
     */
	public function handleWinner(Player $player = null): void
	{
	    if($player === null) {
	        $player = $this->getWinner();
	        if($player === null)
	            return;
        }


		(new PlayerDuelEvent($this, $player))->call();

		$name = $player->getName();

		$this->scoreboardTask->resetPlayersTextByName($name);
		$this->bossbarTask->resetPlayersTextByName($name);
		$this->bossbarTask->resetBarProgressByName($name);

		$player->removeAllEffects();

		$player->setImmobile(false);

		$player->setHealth((float)$player->getMaxHealth());

		$player->extinguish();

	//	echo "\nWTF";

        $server = $player->getServer();

		$this->broadcastEndGame($server, $player);

		$player->teleport($server->getWorldManager()->getDefaultWorld()->getSpawnLocation());

	}
    /**
     * @param Server $server
     * @param Player $player
     */
	public function broadcastEndGame(Server $server, Player $player): void
	{
		$deadPlayer = array_values($this->removedPlayers)[0];
		$server->broadcastMessage(str_replace(['{winner}', '{loser}', '{type}'], [$player->getDisplayName(), $deadPlayer->getDisplayName(), $this->kitType], static::END_MESSAGE));
	}
	/**
	 * Safely end the match
	 * @throws ReflectionException
	 */
	public function stopMatch(): void
	{
		$this->stage = Heartbeat::STAGE_FINISHED;
		$this->manager->removeMatch($this);
		foreach ($this->players as $player)
			$this->removeFromPlayers($player->getName());
		$this->removeSpectators();
		$this->getHandler()->remove();
	}

	public function givePlayersKits(): void
	{
		$server = $this->duels->getServer();
		foreach ($this->players as $player)
			$server->dispatchCommand($player, str_replace('{type}', $this->kitType, $this->duels->duelConfig['Settings']['Kit-Command']), true);
	}
	/**
	 * @param string $loserName
	 * @return bool
	 * @throws ReflectionException
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
		}

		$this->scoreboardTask->resetPlayersTextByName($loserName); // Reset their scoreboard
		$this->bossbarTask->resetPlayersTextByName($loserName);
		$this->bossbarTask->resetBarProgressByName($loserName);

		$this->removedPlayers[$loserName] = $this->players[$loserName];

		unset($this->players[$loserName]);

		$player = Server::getInstance()->getPlayerExact($loserName);

		if($player !== null) {
			$player->setImmobile(false);
			(new PlayerLoseEvent($this, $player))->call();
		}
		return true;
	}
	/**
	 * @return Player[]
	 */
	public function getPlayers(): array
	{
		return $this->players;
	}
	/**
	 * @return Player[]
	 */
	public function getRemovedPlayers(): array
	{
		return $this->removedPlayers;
	}
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function addSpectator(Player $player): bool
	{
		$name = $player->getName();
		if(isset($this->spectatingPlayers[$name]))
			return false;
		$this->spectatingPlayers[$name] = $player;
		$player->teleport(Position::fromObject($this->getArena()->getLocation(), $player->getServer()->getWorldManager()->getWorldByName($this->getTempLevelName())));
		$player->setGamemode(GameMode::SPECTATOR());
		$player->sendMessage(TextFormat::GREEN . 'You are now spectating, do /hub to go back to the lobby!');
		return true;
	}
	/**
	 * Delete all spectators
	 * @param bool $teleport
	 */
	public function removeSpectators(bool $teleport = true): void
	{
		foreach ((array)$this->spectatingPlayers as $player)
			$this->removeSpectator($player, $teleport);
	}
	/**
	 * @param Player $player
	 * @param bool $teleport
	 */
	public function removeSpectator(Player $player, bool $teleport = false): void
	{
		$player->setGamemode(GameMode::SURVIVAL());
		$name = $player->getName();
		unset($this->spectatingPlayers[$name]);
		$this->scoreboardTask->resetPlayersTextByName($name);
		$this->bossbarTask->resetPlayersTextByName($name);
		$this->bossbarTask->resetBarProgressByName($name);
		$player->sendMessage(TextFormat::GREEN . 'You are no longer spectating!');
		if($teleport)
			$player->teleport($this->duels->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
	}
	/**
	 * @return array|null
	 */
	public function getSpectators(): ?array
	{
		return $this->spectatingPlayers;
	}
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function isInMatch(Player $player): bool
	{
		return in_array($player, $this->players, true);
	}
	/**
	 * @return string
	 */
	public function getKitType(): string
	{
		return $this->kitType;
	}
	/**
	 * @return int
	 */
	public function getStage(): int
	{
		return $this->stage;
	}
	/**
	 * @return Arena
	 */
	public function getArena(): Arena
	{
		return $this->arena;
	}
	/**
	 * @return DuelLevel
	 */
	public function getLevel(): DuelLevel
	{
		return $this->level;
	}
	/**
	 * @return string
	 */
	public function getTempLevelName(): string
	{
		return $this->tempLevel;
	}
	/**
	 * Default to unranked
	 *
	 * @return int
	 */
	public function getRanked(): int
	{
		return QueueManager::UNRANKED;
	}
}