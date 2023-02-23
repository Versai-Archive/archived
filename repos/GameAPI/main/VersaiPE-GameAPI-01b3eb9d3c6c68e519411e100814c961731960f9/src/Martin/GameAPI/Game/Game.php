<?php


namespace Martin\GameAPI\Game;

use Exception;
use Martin\GameAPI\Event\PlayerDeathEvent;
use Martin\GameAPI\Game\Maps\Map;
use Martin\GameAPI\Game\Position\GamePosition;
use Martin\GameAPI\Game\Settings\GameRules;
use Martin\GameAPI\Game\Settings\GameSettings;
use Martin\GameAPI\Game\Team\Team;
use Martin\GameAPI\GamePlugin;
use Martin\GameAPI\Task\Async\RemoveDirectoryAsyncTask;
use Martin\GameAPI\Task\CloneWorldTask;
use Martin\GameAPI\Types\GameStateType;
use Martin\GameAPI\Utils\StringUtils;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

abstract class Game
{
    private GamePlugin $plugin;

    /** @var Team[] */
    private array $teams = [];

    /** @var string[] */
    private array $spectators = [];

    private GameSettings $gameSettings;

    private GameRules $gameRules;

    private ?Map $map = null;

    private int $currentState = GameStateType::STATE_WAITING;
    private string $temporaryLevelName;

    private string $code;
    private Player $creator;
    private bool $private = false;

    private GamePosition $spectatorPosition;

    /**
     * Game constructor.
     * @param GamePlugin $plugin
     * @param Player $creator
     * @param Map $map
     * @param Team[] $teams
     * @param GameSettings|null $gameSettings
     * @param GameRules|null $gameRules
     * @throws Exception
     */
    public function __construct(GamePlugin $plugin, Player $creator, Map $map, array $teams, GameSettings $gameSettings = null, GameRules $gameRules = null)
    {
        $this->plugin = $plugin;
        if ($gameSettings === null) {
            $this->gameSettings = $plugin->getGameSettings();
        } else {
            $this->gameSettings = $gameSettings;
        }

        if ($gameRules === null) {
            $this->gameRules = $plugin->getGameRules();
        } else {
            $this->gameRules = $gameRules;
        }

        $this->creator = $creator;
        $this->code = StringUtils::generateCode();
        $this->map = $map;
        $this->temporaryLevelName = "gameapi-" . $map->getName() . "-" . $creator->getName() . random_int(1000, 9999);

        $this->getPlugin()->getScheduler()->scheduleTask(new CloneWorldTask($map->getWorld(), $this->temporaryLevelName));

        # $this->getPlugin()->getServer()->loadLevel($this->temporaryLevelName);
        # $this->level = $this->getPlugin()->getServer()->getLevelByName($this->temporaryLevelName);

        $this->registerTeams($teams);
    }

    /**
     * @return GamePlugin
     */
    public function getPlugin(): GamePlugin
    {
        return $this->plugin;
    }

    /**
     * @param array $teams
     */
    public function registerTeams(array $teams): void
    {
        foreach ($teams as $team) {
            if (!($team instanceof Team)) continue;
            $this->registerTeam($team);
        }
    }

    /**
     * @param Team $team
     * @return bool Whether if it is already registered or not
     */
    public function registerTeam(Team $team): bool
    {
        if (isset($this->teams[$team->getIdentifier()])) {
            return false;
        }

        if (in_array($team, $this->teams, true)) {
            return false;
        }

        $this->teams[$team->getIdentifier()] = $team;
        return true;
    }

    public function getLevel(): ?Level
    {
        return Server::getInstance()->getLevelByName($this->temporaryLevelName);
    }

    /**
     * @description When the game starts
     */
    abstract public function startGame(): void;

    /**
     * @description When the game ends
     */
    abstract public function endGame(Team $winner): void;

    /**
     * @description When a player dies
     */
    abstract public function onDeath(PlayerDeathEvent $event): void;

    public function getTeamByPlayer($player): ?Team
    {
        if ($player instanceof Player) {
            $player = $player->getLowerCaseName();
        } else {
            $player = strtolower($player);
        }
        foreach ($this->getTeams() as $team) {
            if ($team->inTeam($player)) {
                return $team;
            }
        }

        return null;
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    public function broadcast(string $message, array $excluded = []): void
    {
        foreach ($this->getTeams() as $team) {
            $team->broadcast($message, $excluded);
        }

        foreach ($this->getSpectators() as $spectator) {
            if (in_array($spectator->getLowerCaseName(), $excluded, true)) {
                continue;
            }

            $spectator->sendMessage($message);
        }
    }

    /**
     * @return Player[]
     */
    public function getSpectators(): array
    {
        $players = [];

        foreach ($this->spectators as $spectator => $state) {
            $player = $this->getPlugin()->getServer()->getPlayerExact($spectator);
            if ($player === null) {
                unset($this->spectators[$spectator]);
                continue;
            }

            $players[] = $player;
        }

        return $players;
    }

    /**
     * @param int|null $state
     * @param bool $spectators
     * @return Player[]
     */
    public function getPlayers(?int $state = null, bool $spectators = false): array
    {
        $players = [];

        foreach ($this->getTeams() as $team) {
            foreach ($team->getPlayers($state) as $player) {
                if (in_array($player, $players, true)) {
                    $team->removePlayer($player);
                    continue;
                }

                $players[] = $player;
            }
        }

        if ($spectators === true) {
            foreach ($this->getSpectators() as $spectator) {
                $players[] = $spectator;
            }
        }

        return $players;
    }

    public function toTeam(Player $player, int $teamId): bool
    {
        if (($game = $this->getPlugin()->inGame($player)) !== null) {
            if ($game !== $this) {
                return false;
            }

            $team = $this->getTeam($teamId);
            if ($team === null) {
                return false;
            }

            if ($team->inTeam($player)) {
                return false;
            }

            foreach ($this->getTeams() as $teamLoop) {
                if ($teamLoop !== $team) {
                    $teamLoop->removePlayer($player);
                }
            }

            $team->addPlayer($player);
            return true;
        }

        $team = $this->getTeam($teamId);
        if ($team === null) {
            return false;
        }

        $team->addPlayer($player);
        return true;
    }

    public function getTeam(int $id): ?Team
    {
        foreach ($this->getTeams() as $_id => $team) {
            if ($_id === $id) {
                return $team;
            }

        }
        return null;
    }

    public function close(): void
    {
        if (isset($this->level)) {
            $this->getPlugin()->getServer()->unloadLevel($this->level);
        }

        $this->getPlugin()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick): void {
            $this->getPlugin()->getServer()->getAsyncPool()->submitTask(new RemoveDirectoryAsyncTask($this->getPlugin()->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $this->temporaryLevelName . DIRECTORY_SEPARATOR));
            $this->getPlugin()->removeGame($this);
        }), 1);
    }

    public function removeSpectator(Player $player): bool
    {
        if (empty($this->spectators[$player->getLowerCaseName()])) {
            return false;
        }

        unset($this->spectators[$player->getLowerCaseName()]);
        return true;
    }

    /**
     * @return GameSettings
     */
    public function getGameSettings(): GameSettings
    {
        return $this->gameSettings;
    }

    /**
     * @param GameSettings $gameSettings
     */
    public function setGameSettings(GameSettings $gameSettings): void
    {
        $this->gameSettings = $gameSettings;
    }

    public function addSpectator(Player $player): bool
    {
        if (isset($this->spectators[$player->getLowerCaseName()])) {
            return false;
        }

        $this->spectators[$player->getLowerCaseName()] = 0;
        return true;
    }

    public function getTeamWithLessPlayers(): ?Team
    {
        $leastAmountOfPlayers = null;
        foreach ($this->getTeams() as $team) {
            if (is_null($leastAmountOfPlayers)) {
                $leastAmountOfPlayers = $team;
                continue;
            }

            if (count($team->getPlayers()) < count($leastAmountOfPlayers->getPlayers())) {
                $leastAmountOfPlayers = $team;
            }
        }

        return $leastAmountOfPlayers;
    }

    public function removePlayer(Player $player): bool
    {
        foreach ($this->getTeams() as $team) {
            if ($team->removePlayer($player)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return GameRules
     */
    public function getGameRules(): GameRules
    {
        return $this->gameRules;
    }

    /**
     * @param GameRules $gameRules
     */
    public function setGameRules(GameRules $gameRules): void
    {
        $this->gameRules = $gameRules;
    }

    /**
     * @return int
     */
    public function getCurrentState(): int
    {
        return $this->currentState;
    }

    /**
     * @param int $currentState
     */
    public function setCurrentState(int $currentState): void
    {
        $this->currentState = $currentState;
    }

    /**
     * @return Map
     */
    public function getMap(): Map
    {
        return $this->map;
    }

    /**
     * @param Map|null $map
     */
    public function setMap(?Map $map): void
    {
        $this->map = $map;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Player
     */
    public function getCreator(): Player
    {
        return $this->creator;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    /**
     * @return GamePosition
     */
    public function getSpectatorPosition(): GamePosition
    {
        return $this->spectatorPosition;
    }
}