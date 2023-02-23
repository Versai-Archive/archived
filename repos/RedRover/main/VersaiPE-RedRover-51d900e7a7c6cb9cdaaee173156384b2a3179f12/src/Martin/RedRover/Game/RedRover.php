<?php


namespace Martin\RedRover\Game;


use Martin\GameAPI\Event\PlayerDeathEvent;
use Martin\GameAPI\Game\Game;
use Martin\GameAPI\Game\Maps\Map;
use Martin\GameAPI\Game\Position\GamePosition;
use Martin\GameAPI\Game\Team\Team;
use Martin\GameAPI\GamePlugin;
use Martin\GameAPI\Kit\IKit;
use Martin\GameAPI\Kit\KitManager;
use Martin\GameAPI\Task\PlayerCountdownTask;
use Martin\GameAPI\Task\SleepTask;
use Martin\GameAPI\Types\GameStateType;
use Martin\GameAPI\Types\PlayerStateType;
use Martin\RedRover\Main;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RedRover extends Game
{
    public const TEAM_RED = 0;
    public const TEAM_BLUE = 1;
    public const TEAM_SPECTATOR = 2;

    public const USED_KIT = "nodebuff";

    private ?array $lastDeathInformation = null;

    private bool $private;
    private Player $creator;

    private GamePosition $spectatorPosition;

    public function __construct(Main $plugin, Player $creator, Map $map, bool $private)
    {
        parent::__construct($plugin, $creator, $map, [], $plugin->getGameSettings(), $plugin->getGameRules());

        $this->creator = $creator;
        $this->private = $private;

        $this->registerTeam(new Team(self::TEAM_RED, "Red", TextFormat::RED, 2, 8));
        $this->registerTeam(new Team(self::TEAM_BLUE, "Blue", TextFormat::BLUE, 2, 8));

        $plugin->getServer()->broadcastMessage($this->getPlugin()->getMessage("broadcasts.event-init", ["player" => $creator->getName()]));

        $this->getRedTeam()->setPosition($map->getPositions()[0]);
        $this->getBlueTeam()->setPosition($map->getPositions()[1]);

        $this->spectatorPosition = $map->getPositions()[2];
    }

    /**
     * @return Main
     */
    public function getPlugin(): GamePlugin
    {
        return parent::getPlugin();
    }

    public function getRedTeam(): Team
    {
        return $this->getTeams()[self::TEAM_RED];
    }

    public function getBlueTeam(): Team
    {
        return $this->getTeams()[self::TEAM_BLUE];
    }

    public function startGame(): void
    {
        $this->broadcast($this->getPlugin()->getMessage("broadcasts.event-started", ["map" => $this->getMap()->getName(), "author" => $this->getMap()->getAuthor(), "player" => $this->creator->getName()]));
        $this->startRound();
        $this->setCurrentState(GameStateType::STATE_ONGOING);
    }

    public function startRound(): void
    {
        /** @var Player $red_player */
        $red_player = $this->getPlayingPlayer(self::TEAM_RED);
        /** @var Player $blue_player */
        $blue_player = $this->getPlayingPlayer(self::TEAM_BLUE);

        if ($red_player === null) {
            $red_player = $this->getRandomPlayer(self::TEAM_RED);
            if ($red_player === null) {
                $this->endGame($this->getBlueTeam());
                return;
            }
        }

        if (is_null($blue_player)) {
            $blue_player = $this->getRandomPlayer(self::TEAM_BLUE);
            if (is_null($blue_player)) {
                $this->endGame($this->getRedTeam());
                return;
            }
        }

        $this->broadcast($this->getPlugin()->getMessage("broadcasts.game.round-start", ["blue" => TextFormat::BLUE . $blue_player->getName(), "red" => TextFormat::RED . $red_player->getName()]));

        $this->getRedTeam()->setState($red_player, PlayerStateType::STATE_PLAYING);
        $this->getBlueTeam()->setState($blue_player, PlayerStateType::STATE_PLAYING);

        foreach ($this->getSpectators() as $spectator) {
            GamePosition::teleport($spectator, $this, $this->spectatorPosition);
        }

        GamePosition::teleport($red_player, $this, $this->getRedTeam()->getPosition());
        GamePosition::teleport($blue_player, $this, $this->getBlueTeam()->getPosition());

        foreach ([$red_player, $blue_player] as $player) {
            $player->setImmobile(true);
            if (is_null($this->lastDeathInformation)) {
                KitManager::toPlayer(KitManager::getKit(self::USED_KIT), $player);
            } else {
                if (isset($this->lastDeathInformation["team"]) && $this->lastDeathInformation["team"] === self::TEAM_RED) {
                    if (isset($this->lastDeathInformation["kit"]) && ($kit = $this->lastDeathInformation["kit"]) instanceof IKit) {
                        KitManager::toPlayer($kit, $red_player);
                    } else {
                        KitManager::toPlayer(KitManager::getKit(self::USED_KIT), $red_player);
                    }
                }

                if (isset($this->lastDeathInformation["team"]) && $this->lastDeathInformation["team"] === self::TEAM_BLUE) {
                    if (isset($this->lastDeathInformation["kit"]) && ($kit = $this->lastDeathInformation["kit"]) instanceof IKit) {
                        KitManager::toPlayer($kit, $blue_player);
                    } else {
                        KitManager::toPlayer(KitManager::getKit(self::USED_KIT), $blue_player);
                    }
                }
            }
        }

        new PlayerCountdownTask($this->getPlugin(), [$red_player, $blue_player], 10, $this->getPlugin()->getMessage("title.countdown", [], false), function () use ($red_player, $blue_player): void {
            foreach ([$red_player, $blue_player] as $player) {
                $player->setImmobile(false);
                $player->sendTitle($this->getPlugin()->getMessage("title.finish", [], false));
            }
        });
    }

    public function getPlayingPlayer(int $team): ?Player
    {
        switch ($team) {
            case self::TEAM_RED:
            {
                $players = $this->getRedTeam()->getPlayers(PlayerStateType::STATE_PLAYING);
                if (count($players) === 1) {
                    return $players[0];
                }

                if (count($players) > 1) {
                    return $players[0];
                }

                return null;
            }

            case self::TEAM_BLUE:
            {
                $players = $this->getBlueTeam()->getPlayers(PlayerStateType::STATE_PLAYING);
                if (count($players) === 1) {
                    return $players[0];
                }

                if (count($players) > 1) {
                    return $players[0];
                }

                return null;
            }
            default:
                return null;
        }
    }

    public function getRandomPlayer(int $team): ?Player
    {
        switch ($team) {
            case self::TEAM_RED:
            {
                $players = $this->getRedTeam()->getPlayers(PlayerStateType::STATE_WAITING);
                if (count($players) >= 1) {
                    return $players[array_rand($players)];
                }

                return null;
            }

            case self::TEAM_BLUE:
            {
                $players = $this->getBlueTeam()->getPlayers(PlayerStateType::STATE_WAITING);
                if (count($players) >= 1) {
                    return $players[array_rand($players)];
                }

                return null;
            }
            default:
                return null;
        }
    }

    public function endGame(Team $winner): void
    {
        $this->setCurrentState(GameStateType::STATE_END);

        $loser = $winner->getIdentifier() === self::TEAM_RED ? $this->getBlueTeam() : $this->getRedTeam();

        $this->broadcast($this->getPlugin()->getMessage("broadcasts.event-ended", ["winner" => $winner->toString(), "loser" => $loser->toString(), "player" => $this->getCreator()->getName()]));


        foreach ($this->getPlayers() as $player) {
            if ($this->getPlugin()->getEndType() === Main::END_TYPE_COMMAND) {
                $player->chat("/" . $this->getPlugin()->getEndCommand());
            } else if ($this->getPlugin()->getServer()->getDefaultLevel()) {
                $player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSpawnLocation());
            } else {
                $player->chat("/" . $this->getPlugin()->getEndCommand());
            }
        }

        new SleepTask($this->getPlugin(), function (): void {
            $this->close();
        }, 20);
    }

    /**
     * @return Player[]
     */
    public function getSpectators(): array
    {
        return array_merge(parent::getSpectators(), $this->getPlayers(PlayerStateType::STATE_WAITING), $this->getPlayers(PlayerStateType::STATE_DEAD));
    }

    public function removePlayer(Player $player): bool
    {
        $this->broadcast($this->getPlugin()->getPrefix() . $player->getName() . " §chas been removed out of the event.");
        return parent::removePlayer($player); // TODO: Change the autogenerated stub
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        $team = $this->getTeamByPlayer($event->getPlayer());
        if (is_null($team)) {
            $this->lastDeathInformation = null;
            $this->startRound();
            return;
        }

        $team->setState($event->getPlayer(), PlayerStateType::STATE_DEAD);

        $this->lastDeathInformation = [
            "team" => $team->getIdentifier(),
            "kit" => KitManager::fromPlayer($event->getPlayer())
        ];

        $this->startRound();
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param Player $creator
     */
    public function setCreator(Player $creator): void
    {
        $this->creator = $creator;
    }
}
