<?php


namespace Martin\GameAPI;


use Martin\GameAPI\Game\Game;
use Martin\GameAPI\Game\Maps\Map;
use Martin\GameAPI\Game\Settings\GameRules;
use Martin\GameAPI\Game\Settings\GameSettings;
use Martin\GameAPI\Game\Team\Team;
use Martin\GameAPI\Listener\GameWorldRuleListener;
use Martin\GameAPI\Listener\PlayerListener;
use Martin\GameAPI\Task\Async\RemoveDirectoryAsyncTask;
use Martin\GameAPI\Utils\StringUtils;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\Server;

abstract class GamePlugin extends PluginBase
{
    protected GameSettings $gameSettings;

    protected GameRules $gameRules;

    /** @var Map[] */
    protected array $maps = [];
    /** @var Team[] */
    protected array $skeletonTeams = [];
    /** @var Game[] */
    protected array $games = [];

    public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file)
    {
        $this->gameSettings = new GameSettings();
        $this->gameRules = new GameRules();
        parent::__construct($loader, $server, $description, $dataFolder, $file);
    }

    public function onDisable(): void
    {
        foreach ($this->getGames() as $game) {
            $game->close();
        }
    }

    /**
     * @return Game[]
     */
    public function getGames(): array
    {
        return $this->games;
    }

    public function onEnable(): void
    {
        $directory = $this->getServer()->getDataPath() . DIRECTORY_SEPARATOR . "worlds" . DIRECTORY_SEPARATOR;
        foreach (scandir($directory) as $world) {
            if (StringUtils::startsWith($world, "gameapi")) {
                $this->getServer()->getAsyncPool()->submitTask(new RemoveDirectoryAsyncTask($directory . $world . DIRECTORY_SEPARATOR));
            }
        }
    }

    public function queueGame(Game $game): Game
    {
        $game->registerTeams($this->skeletonTeams);
        $game->setGameSettings($this->gameSettings);
        $game->setGameRules($this->gameRules);

        $this->games[] = $game;
        return $game;
    }

    public function registerListeners(): void
    {
        $this->registerListener(new GameWorldRuleListener($this));
        $this->registerListener(new PlayerListener($this));
    }

    public function registerListener(Listener $listener): void
    {
        $this->getServer()->getPluginManager()->registerEvents($listener, $this);
    }

    public function inGame(Player $player): ?Game
    {
        foreach ($this->games as $game) {
            foreach ($game->getTeams() as $team) {
                foreach ($team->getPlayers() as $playerLoop) {
                    if ($player === $playerLoop) {
                        return $game;
                    }
                }
            }

            foreach ($game->getSpectators() as $playerLoop) {
                if ($player === $playerLoop) {
                    return $game;
                }
            }
        }

        return null;
    }

    public function registerTeams(Team ...$teams): void
    {
        foreach ($teams as $team) {
            if (isset($this->skeletonTeams[$team->getIdentifier()])) {
                continue;
            }
            $this->skeletonTeams[$team->getIdentifier()] = $team;
        }
    }

    public function getGameByPlayer(Player $player): ?Game
    {
        foreach ($this->getGames() as $game) {
            foreach (array_merge($game->getPlayers(), $game->getSpectators()) as $player_2) {
                if ($player === $player_2) {
                    return $game;
                }
            }
        }

        return null;
    }

    public function getGameByCode(string $code): ?Game
    {
        $code = strtoupper($code);
        foreach ($this->getGames() as $game) {
            if ($game->getCode() === $code) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return $this->skeletonTeams;
    }

    /**
     * @return GameSettings
     */
    public function getGameSettings(): GameSettings
    {
        return $this->gameSettings;
    }

    /**
     * @return GameRules
     */
    public function getGameRules(): GameRules
    {
        return $this->gameRules;
    }

    public function removeGame(Game $game): bool
    {
        if (!in_array($game, $this->games, true)) {
            return false;
        }

        unset($this->games[array_search($game, $this->games, true)]);
        return true;
    }
}