<?php


namespace Versai\Sumo\Session;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use Versai\Sumo\Sumo;
use Versai\Sumo\Task\CountdownTask;

/**
 * Class Session
 * @description Sumo Session saving data
 * @package Versai\Sumo\Session
 */
class Session
{
    public const PLAYER_STATE_WAITING = 0;
    public const PLAYER_STATE_PLAYING = 1;
    public const PLAYER_STATE_SPECTATING = 2;

    public const GAME_STATE_WAITING = 0;
    public const GAME_STATE_ONGOING = 1;

    /** @var string[] */
    public array $players = [];

    public Level $level;
    
    public Vector3 $joiningPosition;
    
    public Vector3 $playingPosition1;
    
    public Vector3 $playingPosition2;

    public ?string $lastWinningPerson = null;

    public int $currentState = self::GAME_STATE_WAITING;

    public int $timeStarted;

    private Sumo $sumo;

    public function __construct(Sumo $sumo, Level $level, Vector3 $joiningPosition, Vector3 $playingPosition1, Vector3 $playingPosition2) {
        $this->sumo = $sumo;
        $this->level = $level;
        $this->joiningPosition = $joiningPosition;
        $this->playingPosition1 = $playingPosition1;
        $this->playingPosition2 = $playingPosition2;
        $this->timeStarted = time();
    }

    public function addPlayer(Player $player): bool {
        if (isset($this->players[$player->getName()])) return false;
        $this->players[$player->getName()] = self::PLAYER_STATE_WAITING;
    }

    public function removePlayer(Player $player): bool {
        if (empty($this->players[$player->getName()])) return false;
        unset($this->players[$player->getName()]);
    }

    public function getWaitingPlayers(): array {
        return array_filter($this->players, function (string $player, int $value) {
            return $value === self::PLAYER_STATE_WAITING;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function sendMessage(string $message): void {
        foreach ($this->players as $player) {
            $player = Server::getInstance()->getPlayer($player);
            if ($player) $player->sendMessage($message);
        }
    }

    public function startMatch(): bool {
        if ($this->currentState === self::GAME_STATE_ONGOING) return false;
        $this->currentState = self::GAME_STATE_ONGOING;
        return true;
    }

    public function startRound(): bool {
        if ($this->currentState === self::GAME_STATE_WAITING) return false;
        if ($this->lastWinningPerson === null) {
            $this->lastWinningPerson = array_rand($this->getWaitingPlayers());
            $this->players[$this->lastWinningPerson] = self::PLAYER_STATE_PLAYING;
        }

        $fightingPlayer = array_rand($this->getWaitingPlayers());
        $this->players[$fightingPlayer] = self::PLAYER_STATE_PLAYING;

        $playerA = Server::getInstance()->getPlayer($this->lastWinningPerson);
        $playerB = Server::getInstance()->getPlayer($fightingPlayer);

        $playerA->setImmobile(true);
        $playerB->setImmobile(true);

        $playerA->teleport($this->playingPosition1);
        $playerB->teleport($this->playingPosition2);

        new CountdownTask($this->sumo, 10, [$playerA, $playerB], "", function () use ($playerA, $playerB) {
            $playerA->setImmobile(false);
            $playerB->setImmobile(false);
        });

        return true;
    }
}