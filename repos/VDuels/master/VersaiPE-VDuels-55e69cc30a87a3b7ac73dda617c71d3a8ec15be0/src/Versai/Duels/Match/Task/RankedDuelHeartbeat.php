<?php
declare(strict_types=1);

namespace Versai\Duels\Match\Task;

use pocketmine\utils\TextFormat;
use Versai\Duels\Elo\Rating;
use Versai\Duels\Queue\QueueManager;
use Duo\vpractice\PracticeStats;

class RankedDuelHeartbeat extends UnrankedDuelHeartbeat
{

    protected const TYPE            = 'Ranked';
    protected const DUEL_LENGTH_KEY = '1v1-Duel-Length';
    protected const END_MESSAGE     = TextFormat::GOLD . '{winner} won a ranked match against {loser} with type {type}!';

    /**
     * @param string $loserName
     * @return bool
     */
    public function removeFromPlayers(string $loserName): bool
    {
        if (!isset($this->players[$loserName])) {
            $this->duels->getLogger()->error("There was a glitch! - The players names must be the keys!");
            return false;
        }

        if (count($this->players) === 2) {
            if ($this->stage !== Heartbeat::STAGE_FINISHED) {
                $this->stage = Heartbeat::STAGE_FINISHED;
                $this->matchEndTimer = $this->duels->duelConfig['Settings']['End-Game-Time'];
            }

            $this->handleElo($loserName);

        }
        $this->removedPlayers[$loserName] = $this->players[$loserName];
        unset($this->players[$loserName]);
        return true;
    }

    /**
     * Default to unranked
     *
     * @return int
     */
    public function getRanked(): int
    {
        return QueueManager::RANKED;
    }

    /**
     * @param string $loserName
     */
    public function handleElo(string $loserName): void
    {
        $players = $this->getPlayers();
        $loser = $players[$loserName];

        $loserSession = PracticeStats::getInstance()->getPlayerManager()->getSession($loser);
        $loserElo = ($loserSession?->getElo($this->getKitType()) ?? 500);

        foreach ($players as $player) {
            if ($players[$loserName] !== $player) {
                $winner = $player;
            }
        }

        if (isset($winner)) {
            $winnerSession = PracticeStats::getInstance()->getPlayerManager()->getSession($winner);
            $winnerElo = $winnerSession?->getElo($this->getKitType()) ?? 500;
            $rating = new Rating($winnerElo, $loserElo, Rating::WIN, Rating::LOSS);
            $ratings = $rating->calculate();

            $winnerSession?->setElo($this->getKitType(), $ratings['a']);
            $winner->sendTitle(TextFormat::GREEN . '+' . $ratings['a'] - $winnerElo, '   Win', 5, 10, 5);

            $loserSession?->setElo($this->getKitType(), $ratings['b']);
            $loser->sendTitle(TextFormat::RED . '-' . $loserElo - $ratings['b'], '  Loss', 5, 10, 5);
        } else {
            $this->duels->getLogger()->error("[ELO] There was a glitch! - Winner not found");
        }
    }

}