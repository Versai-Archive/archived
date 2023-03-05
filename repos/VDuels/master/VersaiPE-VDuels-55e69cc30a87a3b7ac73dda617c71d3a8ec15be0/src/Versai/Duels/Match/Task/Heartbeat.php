<?php
declare(strict_types=1);

namespace Versai\Duels\Match\Task;

use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\sound\ClickSound;
use ReflectionException;
use Versai\arenas\Arena;
use Versai\Duels\Duels;
use Versai\Duels\Events\MatchStartEvent;
use Versai\Duels\Events\PlayerDuelEvent;
use Versai\Duels\Events\PlayerLoseEvent;
use Versai\Duels\Level\DuelLevel;
use Versai\Duels\Match\DuelManager;
use Versai\Duels\Queue\QueueManager;
use Duo\kits\Kits;
use function array_keys;
use function array_merge;
use function array_values;
use function str_replace;
use function strtolower;

class Heartbeat extends Task {

	protected const END_MESSAGE = TextFormat::GOLD . '{winner} won a duel!'; # Generic message :P

	public const STAGE_COUNTDOWN = 1;
	public const STAGE_PLAYING = 2;
	public const STAGE_FINISHED = 3;

	protected const DUEL_LENGTH_KEY = 'Generic';

	/** @var Duels $duels */
	protected Duels $duels;
	/** @var DuelManager $manager */
	protected DuelManager $manager;
	/** @var int $countdown */
	protected int $countdown;
	/** @var int $matchEndTimer */
	protected int $matchEndTimer = 4;
	/** @var DuelLevel $level */
	protected DuelLevel $level;
	/** @var Player[] */
	protected array $players = [];
	/** @var Player[] */
	protected array $removedPlayers = [];
	/** @var Player[] */
	protected array $spectatingPlayers = [];
	/** @var int $stage */
	protected int $stage = Heartbeat::STAGE_COUNTDOWN;
	/** @var string $kitType */
	protected string $kitType;
	/** @var string $tempLevel */
	protected string $tempLevel;
	/** @var Arena $arena */
	private Arena $arena;
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
	public function __construct(Duels $duels, DuelManager $manager, DuelLevel $level, array $players, string $kitType, string $levelName, Arena $arena) {
		$this->duels = $duels;
		$this->countdown = $this->duels->duelConfig['Settings'][static::DUEL_LENGTH_KEY];
		$this->manager = $manager;
		$this->level = $level;
		$this->players = $players;
		$this->kitType = $kitType;
		$this->tempLevel = $levelName;
		$this->arena = $arena;

		$this->preparePlayers($players);
	}

	/**
	 * @param Player[] $players
	 */
	public function preparePlayers(array $players): void {
		foreach ($players as $player) {
			$player->setImmobile();
			$player->setGamemode(GameMode::SURVIVAL());
			$player->getInventory()->clearAll();
			$player->getArmorInventory()->clearAll();
			$player->setHealth((float)$player->getMaxHealth());
			$player->getHungerManager()->setFood(20);
			$player->getHungerManager()->setSaturation(20);
			$player->getEffects()->clear();
		}
	}
	/**
	 * @throws ReflectionException
	 */
	public function onRun(): void{

		if($this->spectatingPlayers !== null) {
			$this->sendSpectatorsScoreboard();
		}


		if ($this->stage === Heartbeat::STAGE_FINISHED && is_array($this->removedPlayers)) {

			$this->sendEndMatchScoreboard();

			if ($this->matchEndTimer === 0) {
			    $player = $this->getWinner();
                $this->stopMatch();
                $this->handleWinner($player);
            }
		} else {
			$this->sendPlayersScoreboard();
		}
		$this->matchEndTimer--;

		$diff = max(array_keys($this->duels->countdownArray)) - abs($this->duels->duelConfig['Settings'][static::DUEL_LENGTH_KEY] - $this->countdown);

		if (isset($this->duels->countdownArray[$diff])) {

			$bang = explode(':', $this->duels->countdownArray[$diff]);
			$levelName = $this->level->getName();
			$author = $this->level->getAuthor();

			if ($bang[0] !== '-') {
                foreach ($this->players as $player) {
                    $player->sendMessage(str_replace(['{map}', '{author}', '{seconds}'], [$levelName, $author, $diff], $bang[0]));
                }
            }
			if ($bang[1] !== '-') {
                foreach ($this->players as $player) {
                    $player->sendPopup(str_replace(['{map}', '{author}', '{seconds}'], [$levelName, $author, $diff], $bang[1]));
                }
            }
			if ($bang[2] !== '-') {
                foreach ($this->players as $player) {
                    $player->sendTitle(str_replace(['{map}', '{author}', '{seconds}'], [$levelName, $author, $diff], $bang[2]), '', 5, 10, 5);
                }
            }

			foreach ($this->players as $player) {
				$level = $this->duels->getServer()->getWorldManager()->getWorldByName($this->tempLevel);
				if ($level !== null) {
                    $level->addSound($player->getPosition()->asVector3(), new ClickSound());
                }
			}
		}

		if ($diff === 0) {
			if($this->stage === Heartbeat::STAGE_COUNTDOWN) {
                $this->stage = Heartbeat::STAGE_PLAYING;
            } else {
                return;
            }

			(new MatchStartEvent($this))->call();
			$this->givePlayersKits();
			foreach ($this->players as $player) {
                $player->setImmobile(false);
            }
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
		} else {
            $this->countdown--;
        }
	}
	/**
	 * Send players the in match scoreboard
	 */
	protected function sendPlayersScoreboard(): void{}
	/**
	 * Send the winning player(s) the end match scoreboard
	 */
	protected function sendEndMatchScoreboard(): void{}
	/**
	 * Send the spectators a scoreboard
	 */
	protected function sendSpectatorsScoreboard(): void{}
	/**
	 * @param Player $player
	 */
	public function resetAndRemovePlayer(Player $player): void {
	    if(!$player->getPosition()->isValid()) {
            return;
        }
		$player->getEffects()->clear();
		$player->setHealth((float)$player->getMaxHealth());
		$name = $player->getName();
		$player->teleport($this->duels->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
	}

    /**
     * @return null|Player
     */
    public function getWinner(): ?Player {
        $ak = array_keys($this->players);

        if(!isset($ak[0])) {
            return null;
        }

        if($this->stage !== Heartbeat::STAGE_FINISHED) {
            return null;
        }

        return array_values($this->players)[0];
    }

    /**
     * @param Player|null $player
     * @throws ReflectionException
     */
	public function handleWinner(Player $player = null): void {
	    if($player === null) {
            $player = $this->getWinner();
            if ($player === null) {
                return;
            }
        }

	    if(!$player->getPosition()->isValid()) {
            return;
        }

		(new PlayerDuelEvent($this, $player))->call();

		$player->getEffects()->clear();
		$player->setImmobile(false);
		$player->setHealth((float)$player->getMaxHealth());
		$player->extinguish();

        $server = $player->getServer();
		$this->broadcastEndGame($server, $player);
		$player->teleport($server->getWorldManager()->getDefaultWorld()->getSpawnLocation());

	}
    /**
     * @param Server $server
     * @param Player $player
     */
	public function broadcastEndGame(Server $server, Player $player): void {
		$deadPlayer = array_values($this->removedPlayers)[0];
		$server->broadcastMessage(str_replace(['{winner}', '{loser}', '{type}'], [$player->getDisplayName(), $deadPlayer->getDisplayName(), $this->kitType], static::END_MESSAGE));
	}
	/**
	 * Safely end the match
	 * @throws ReflectionException
	 */
	public function stopMatch(): void {
		$this->stage = Heartbeat::STAGE_FINISHED;
		$this->manager->removeMatch($this);
		foreach ($this->players as $player) {
            $this->removeFromPlayers($player->getName());
        }
		$this->removeSpectators();
		$this->getHandler()->remove();
	}

	public function givePlayersKits(): void {
		$server = $this->duels->getServer();
		foreach ($this->players as $player) {
            $kit = Kits::getInstance()->kits[strtolower($this->kitType)];
            $kit->sendKit($player);
            //$server->dispatchCommand($player, str_replace('{type}', $this->kitType, $this->duels->duelConfig['Settings']['Kit-Command']), true);
        }
	}
	/**
	 * @param string $loserName
	 * @return bool
	 * @throws ReflectionException
	 */
	public function removeFromPlayers(string $loserName): bool {
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
	public function getPlayers(): array {
		return $this->players;
	}

	/**
	 * @return Player[]
	 */
	public function getRemovedPlayers(): array {
		return $this->removedPlayers;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function addSpectator(Player $player): bool {
		$name = $player->getName();
		if(isset($this->spectatingPlayers[$name])) {
            return false;
        }
		$this->spectatingPlayers[$name] = $player;
		$player->teleport(Position::fromObject($this->getArena()->getSpawnLocation(), $player->getServer()->getWorldManager()->getWorldByName($this->getTempLevelName())));
		$player->setGamemode(GameMode::SPECTATOR());
		$player->sendMessage(TextFormat::GREEN . 'You are now spectating, do /hub to go back to the lobby!');
		return true;
	}

	/**
	 * Delete all spectators
	 * @param bool $teleport
	 */
	public function removeSpectators(bool $teleport = true): void {
		foreach ((array)$this->spectatingPlayers as $player) {
            $this->removeSpectator($player, $teleport);
        }
	}

	/**
	 * @param Player $player
	 * @param bool $teleport
	 */
	public function removeSpectator(Player $player, bool $teleport = false): void {
		$player->setGamemode(GameMode::SURVIVAL());
		$name = $player->getName();
		unset($this->spectatingPlayers[$name]);
		$player->sendMessage(TextFormat::GREEN . 'You are no longer spectating!');
		if($teleport) {
            $player->teleport($this->duels->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        }
	}

	/**
	 * @return array|null
	 */
	public function getSpectators(): ?array {
		return $this->spectatingPlayers;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function isInMatch(Player $player): bool {
		return in_array($player, $this->players, true);
	}
	/**
	 * @return string
	 */
	public function getKitType(): string {
		return $this->kitType;
	}

	/**
	 * @return int
	 */
	public function getStage(): int {
		return $this->stage;
	}

	/**
	 * @return Arena
	 */
	public function getArena(): Arena {
		return $this->arena;
	}

	/**
	 * @return DuelLevel
	 */
	public function getLevel(): DuelLevel {
		return $this->level;
	}

	/**
	 * @return string
	 */
	public function getTempLevelName(): string {
		return $this->tempLevel;
	}

	/**
	 * Default to unranked
	 *
	 * @return int
	 */
	public function getRanked(): int {
		return QueueManager::UNRANKED;
	}
}