<?php


namespace Martin\Sumo\Game;


use Exception;
use Martin\GameAPI\Event\PlayerDeathEvent;
use Martin\GameAPI\Game\Game;
use Martin\GameAPI\Game\Maps\Map;
use Martin\GameAPI\Game\Position\GamePosition;
use Martin\GameAPI\Game\Team\Team;
use Martin\GameAPI\GamePlugin;
use Martin\GameAPI\Kit\KitManager;
use Martin\GameAPI\Kit\Kits\EmptyKit;
use Martin\GameAPI\Task\PlayerCountdownTask;
use Martin\GameAPI\Types\GameStateType;
use Martin\GameAPI\Types\PlayerStateType;
use Martin\GameAPI\Utils\TeamUtils;
use Martin\Sumo\Main;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Sumo extends Game
{
    public const MIN_PLAYERS = 4;
    public int $totalRounds = 0;
    private array $playersWaiting = [];
    private array $playersSpectating = [];
    private string $playerWonLastRound = "";
    private string $playerRandomlyChoosen = "";

    public function __construct(GamePlugin $plugin, Player $creator, Map $map)
    {
        parent::__construct($plugin, $creator, $map, [], $plugin->getGameSettings(), $plugin->getGameRules());
    }

    public function addPlayer(Player $player): void
    {
        if ($this->getCurrentState() === GameStateType::STATE_WAITING) {
            $this->removePlayer($player);
            $this->playersWaiting[] = $player->getLowerCaseName();
            return;
        }


        $this->removePlayer($player);
        $this->playersSpectating[] = $player->getLowerCaseName();
    }

    public function removePlayer(Player $player): bool
    {
        foreach ($this->playersSpectating as $key => $playerLoop) {
            if ($playerLoop === $player->getLowerCaseName()) {
                unset($this->playersSpectating[$key]);
                return true;
            }
        }

        foreach ($this->playersWaiting as $key => $playerLoop) {
            if ($playerLoop === $player->getLowerCaseName()) {
                unset($this->playersWaiting[$key]);
                return true;
            }
        }

        if ($player->getLowerCaseName() === $this->playerWonLastRound) {
            $this->playerWonLastRound = "";
        }

        if ($player->getLowerCaseName() === $this->playerRandomlyChoosen) {
            $this->playerRandomlyChoosen = "";
        }

        return false;
    }

    public function startGame(): void
    {
        $this->startRound();
        $this->setCurrentState(GameStateType::STATE_ONGOING);
    }

    public function startRound(): void
    {
        if ($this->playerWonLastRound === "") {
            $playerWon = $this->getWaitingPlayer();
            if ($playerWon === null) {
                $this->endGame(TeamUtils::getYellowTeam(0, 0));
                return;
            }

            $this->removePlayer($playerWon);
            $this->playerWonLastRound = $playerWon->getLowerCaseName();
            $this->startRound();
            return;
        }

        $playerWon = $this->getPlugin()->getServer()->getPlayerExact($this->playerWonLastRound);

        if (!$playerWon) {
            $this->playerWonLastRound = "";
            $this->startRound();
            return;
        }

        $randomlyChoosenPlayer = $this->getWaitingPlayer();

        if ($randomlyChoosenPlayer === null) {
            $this->endGame(TeamUtils::getYellowTeam(0, 0));
            return;
        }

        $this->playerRandomlyChoosen = $randomlyChoosenPlayer->getLowerCaseName();

        $spawn1 = $this->getMap()->getPosition(0);
        $spawn2 = $this->getMap()->getPosition(1);
        $spectatorSpawn = $this->getMap()->getPosition(2);

        if (!$spectatorSpawn || !$spawn1 || !$spawn2) {
            return;
        }

        foreach ($this->getSpectators() as $spectator) {
            if (!$spectator instanceof Player) {
                continue;
            }

            if ($spectator->getLevel() !== $this->getLevel()) {
                GamePosition::teleport($spectator, $this, $spectatorSpawn);
            }
        }

        GamePosition::teleport($playerWon, $this, $spawn1);
        GamePosition::teleport($randomlyChoosenPlayer, $this, $spawn2);

        $this->totalRounds++;

        foreach ([$playerWon, $randomlyChoosenPlayer] as $player) {
            if (!$player instanceof Player) {
                $this->startRound();
                return;
            }

            $kit = KitManager::getKit(SumoKit::KIT_NAME);
            if ($kit) {
                KitManager::toPlayer($kit, $player);
            }

            $player->setGamemode(Player::SURVIVAL);
            $player->setImmobile(true);
        }

        $this->broadcast(Main::PREFIX . TextFormat::GRAY . "{$playerWon->getName()} is now fighting against {$randomlyChoosenPlayer->getName()}!");
        new PlayerCountdownTask($this->getPlugin(), [$playerWon, $randomlyChoosenPlayer], 10, TextFormat::BLUE . "Starting in {countdown}", function () use ($playerWon, $randomlyChoosenPlayer): void {
            foreach ([$playerWon, $randomlyChoosenPlayer] as $player) {
                if (!$player instanceof Player) {
                    $this->startRound();
                    return;
                }

                $player->setImmobile(false);
            }
        });
    }

    public function getWaitingPlayer(): ?Player
    {
        if (count($this->playersWaiting) >= 1) {
            $key = array_rand($this->playersWaiting);
            $player = Server::getInstance()->getPlayerExact($this->playersWaiting[$key]);
            if ($player === null) {
                unset($this->playersWaiting[$key]);
                return $this->getWaitingPlayer();
            }

            return $player;
        }

        return null;
    }

    /**
     * @param Team $team
     */
    public function endGame(Team $team): void
    {
        $playerWon = Server::getInstance()->getPlayerExact($this->playerWonLastRound);

        $this->setCurrentState(GameStateType::STATE_END);

        if ($team === self::closingParameter()) {
            $this->broadcast(Main::PREFIX . TextFormat::RED . "§cThis sumo tournament got closed due to the creator closing it!");
        } else if ($playerWon === null) {
            $this->broadcast(Main::PREFIX . TextFormat::GRAY . "§cThis sumo tournament has been ended but no one won it");
        } else {
            $this->broadcast(Main::PREFIX . TextFormat::GRAY . "Player " . TextFormat::BLUE . $playerWon->getName() . TextFormat::GRAY . " won the current sumo tournament! GGs");
            $this->broadcast(Main::PREFIX . TextFormat::GRAY . "Total played rounds: {$this->totalRounds}");
        }

        $onEnd = $this->getPlugin()->getOptions()["onEnd"];
        $endCommand = $this->getPlugin()->getOptions()["endCommand"];

        foreach ($this->getEveryone() as $player) {
            if (!$player instanceof Player) {
                return;
            }

            if ($onEnd === "spawn") {
                $dLevel = $this->getPlugin()->getServer()->getDefaultLevel();
                if ($dLevel) {
                    $player->teleport($dLevel->getSpawnLocation());
                }
            } else {
                $player->chat($endCommand);
            }
        }

        $this->close();
    }

    /**
     * @description Kinda weird
     * @return Team
     */
    public static function closingParameter(): Team
    {
        return new Team(100, "Closing down", "§c", 0, 0);
    }

    public function broadcast(string $message, array $excluded = []): void
    {
        foreach ($this->getEveryone() as $player) {
            if (in_array($player->getLowerCaseName(), $excluded, true)) {
                continue;
            }

            $player->sendMessage($message);
        }
    }

    /**
     * @return Player[]
     */
    public function getEveryone(): array
    {
        $players = [];

        foreach (array_merge_recursive($this->playersWaiting, $this->playersSpectating, [$this->playerWonLastRound, $this->playerRandomlyChoosen]) as $playerString) {
            $player = $this->getPlugin()->getServer()->getPlayerExact($playerString);

            if (!$player instanceof Player) {
                continue;
            }

            $players[] = $player;
        }

        return $players;
    }

    /**
     *
     * @return Main
     */
    public function getPlugin(): GamePlugin
    {
        return parent::getPlugin(); // TODO: Change the autogenerated stub
    }

    /**
     * @return Player[]
     */
    public function getSpectators(): array
    {
        $players = [];

        foreach ($this->playersWaiting as $playerString) {
            $player = $this->getPlugin()->getServer()->getPlayerExact($playerString);
            if ($player === null) {
                unset($this->playersWaiting[array_search($playerString, $this->playersWaiting, true)]);
                continue;
            }

            $players[] = $player;
        }

        foreach ($this->playersSpectating as $playerString) {
            $player = $this->getPlugin()->getServer()->getPlayerExact($playerString);
            if ($player === null) {
                unset($this->playersSpectating[array_search($playerString, $this->playersSpectating, true)]);
                continue;
            }

            $players[] = $player;
        }

        return $players;
    }

    public function getPlayers(?int $state = null, bool $spectators = false): array
    {
        $players = [];

        if ($state === null || $state === PlayerStateType::STATE_WAITING) {
            foreach ($this->playersWaiting as $key => $player) {
                if ($playerServer = Server::getInstance()->getPlayerExact($player)) {
                    $players[] = $playerServer;
                } else {
                    unset($this->playersWaiting[$key]);
                }
            }
        }

        if ($state === null || $state === PlayerStateType::STATE_DEAD || $spectators === true) {
            foreach ($this->playersSpectating as $key => $player) {
                if ($playerServer = Server::getInstance()->getPlayerExact($player)) {
                    $players[] = $playerServer;
                } else {
                    unset($this->playersSpectating[$key]);
                }
            }
        }

        if ($state === null || $state === PlayerStateType::STATE_PLAYING) {
            $playerWon = Server::getInstance()->getPlayerExact($this->playerWonLastRound);
            $playerRandom = Server::getInstance()->getPlayerExact($this->playerRandomlyChoosen);
            if (!$playerWon) {
                $this->playerWonLastRound = "";
            } else {
                $players[] = $playerWon;
            }

            if (!$playerRandom) {
                $this->playerRandomlyChoosen = "";
            } else {
                $players[] = $playerRandom;
            }
        }

        return $players;
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();

        /**   if (!($player->getLowercaseName() === strtolower($this->playerRandomlyChoosen)) || !($player->getLowerCaseName() === strtolower($this->playerWonLastRound))) {
         * var_dump("returning now.........");
         * return;
         * } **/

        if ($player->getLowerCaseName() !== $this->playerRandomlyChoosen) {
            if ($player->getLowerCaseName() !== $this->playerWonLastRound) {
                return;
            }
        }

        if ($player->getLowerCaseName() === $this->playerRandomlyChoosen) {
            $this->playerRandomlyChoosen = "";
        }

        if ($player->getLowerCaseName() === $this->playerWonLastRound) {
            $this->playerWonLastRound = "";
        }

        $winner = $this->getOpposite($player);

        if (!$winner) {
            $this->broadcast(Main::PREFIX . TextFormat::GRAY . "{$player->getName()} lost against an unknown aura");
        } else {
            $this->broadcast(Main::PREFIX . TextFormat::GRAY . "{$winner->getName()} won against {$player->getName()}");
            $this->playerWonLastRound = $winner->getLowerCaseName();
        }

        $this->removePlayer($player);
        $this->addSpectator($player);

        $this->getPlugin()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player): void {
            $kit = KitManager::getKit(EmptyKit::KIT_NAME);
            if ($kit) {
                KitManager::toPlayer($kit, $player);
            }
            GamePosition::teleport($player, $this, $this->getPositionSpectator());
        }), 1);

        $this->startRound();
    }

    public function getOpposite(Player $player): ?Player
    {
        if ($this->playerWonLastRound === $player->getLowerCaseName()) {
            return $this->getPlugin()->getServer()->getPlayerExact($this->playerRandomlyChoosen);
        }

        if ($this->playerRandomlyChoosen === $player->getLowerCaseName()) {
            return $this->getPlugin()->getServer()->getPlayerExact($this->playerWonLastRound);
        }

        return null;
    }

    public function addSpectator(Player $player): bool
    {
        $this->removePlayer($player);
        $this->playersSpectating[] = $player->getLowerCaseName();
        return true;
    }

    public function getPositionSpectator(): GamePosition
    {
        $pos = $this->getMap()->getPosition(2);
        if (!$pos) {
            throw new Exception("Spectator position not found");
        }

        return $pos;
    }

    public function getPositionFirst(): ?GamePosition
    {
        return $this->getMap()->getPosition(0);
    }

    public function getPositionSecond(): ?GamePosition
    {
        return $this->getMap()->getPosition(1);
    }

    /**
     * @return string
     */
    public function getPlayerWonLastRound(): string
    {
        return $this->playerWonLastRound;
    }

    /**
     * @return string
     */
    public function getPlayerRandomlyChoosen(): string
    {
        return $this->playerRandomlyChoosen;
    }

    public function removeRandomPlayer(): void
    {
        $this->playerRandomlyChoosen = "";
    }

    public function removeWinningPlayer(): void
    {
        $this->playerWonLastRound = "";
    }
}