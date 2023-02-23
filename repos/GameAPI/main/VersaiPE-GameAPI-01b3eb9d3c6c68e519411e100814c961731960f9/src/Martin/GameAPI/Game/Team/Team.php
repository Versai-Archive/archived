<?php


namespace Martin\GameAPI\Game\Team;


use Martin\GameAPI\Game\Position\GamePosition;
use Martin\GameAPI\Types\PlayerStateType;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Team
{
    public const UNLIMITED_PLAYER_COUNT = -1;

    private string $name, $formatting;

    private int $identifier, $minimumPlayers, $maximumPlayers;

    /** @var array<string, int> */
    private array $players = [];

    private ?GamePosition $position;

    public function __construct(int $identifier, string $name, string $formatting, int $minimumPlayers, int $maximumPlayers)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->formatting = $formatting;
        $this->minimumPlayers = $minimumPlayers;
        $this->maximumPlayers = $maximumPlayers;
        $this->setPosition(null);
    }

    public function addPlayer(Player $player, int $state = PlayerStateType::STATE_WAITING): bool
    {
        if ($this->isFull()) {
            return false;
        }

        $this->players[$player->getLowerCaseName()] = $state;
        return true;
    }

    public function isFull(): bool
    {
        if ($this->maximumPlayers === -1) {
            return false;
        }

        var_dump(count($this->getPlayers()));
        var_dump($this->getMaximumPlayers());

        // 8 max = 2 players oder 8 max < 2 players
        return !($this->getMaximumPlayers() >= count($this->getPlayers()));
    }

    /**
     * @return Player[]
     */
    public function getPlayers(?int $state = null): array
    {
        $players = [];

        foreach ($this->players as $player_username => $player_state) {
            if ($state === null) {
                $player = Server::getInstance()->getPlayerExact($player_username);
                if ($player === null) {
                    $this->removePlayer($player_username);
                    continue;
                }

                $players[] = $player;
            } else if ($state === $player_state) {
                $player = Server::getInstance()->getPlayerExact($player_username);
                if ($player === null) {
                    $this->removePlayer($player_username);
                    continue;
                }

                $players[] = $player;
            }

        }

        return $players;
    }

    public function removePlayer($player): bool
    {
        if ($player instanceof Player) $player = $player->getLowerCaseName();
        else $player = strtolower($player);

        if (empty($this->players[$player])) return false;
        unset($this->players[$player]);
        return true;
    }

    /**
     * @return int
     */
    public function getMaximumPlayers(): int
    {
        return $this->maximumPlayers;
    }

    public function setState($player, int $state): void
    {
        if ($player instanceof Player) {
            $player = $player->getLowerCaseName();
        } else {
            $player = strtolower($player);
        }
        if (isset($this->players[$player])) {
            $this->players[$player] = $state;
        }
    }

    public function inTeam($player): bool
    {
        if ($player instanceof Player) $player = $player->getLowerCaseName();
        else $player = strtolower($player);
        return isset($this->players[$player]);
    }

    public function checkPlayers(): void
    {
        foreach ($this->players as $player) {
            if (Server::getInstance()->getPlayerExact($player) === null) {
                $this->removePlayer($player);
            }
        }
    }

    public function broadcast(string $message, array $excluded = [], int $stateOnly = -1): void
    {
        foreach ($this->getPlayers($stateOnly) as $player) {
            if (in_array($player->getLowerCaseName(), $excluded, true)) {
                continue;
            }

            $player->sendMessage($message);
        }
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->formatting . $this->name . TextFormat::RESET;
    }

    /**
     * @return int
     */
    public function getMinimumPlayers(): int
    {
        return $this->minimumPlayers;
    }

    /**
     * @return GamePosition
     */
    public function getPosition(): GamePosition
    {
        return $this->position;
    }

    /**
     * @param GamePosition|null $position
     */
    public function setPosition(?GamePosition $position): void
    {
        $this->position = $position;
    }
}