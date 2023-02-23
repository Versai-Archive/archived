<?php


namespace Versai\Sumo\Listener;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Session\Session;
use Versai\Sumo\Sumo;
use Versai\Sumo\Task\CountdownTask;

class GameListener implements Listener
{
    /**
     * @var Sumo
     */
    private Sumo $sumo;

    public function __construct(Sumo $sumo) {
        $this->sumo = $sumo;
    }

    public function onJoin(PlayerJoinEvent $event): void {
        # new CountdownTask($this->sumo, 10, [$event->getPlayer()]);
    }

    public function onPlayerDamageEvent(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        if (!($player instanceof Player)) return;
        if ($event->getCause() !== EntityDamageEvent::CAUSE_VOID) return;
        $session = $this->sumo->getSessionByPlayer($player);
        if (!$session) return;
        if ($session->players[$player->getName()] === Session::PLAYER_STATE_PLAYING) {
            $otherPlayer = array_filter($this->sumo->getFightingPlayers($session), function ($v) use ($player) {
                return $v !== $player->getName();
            });

            if (sizeof($otherPlayer) === 0) {
                # player not found
                $session->lastWinningPerson = null;
                $session->sendMessage(TextFormat::GREEN . $player->getName() . "lost against no one");

            } elseif (sizeof($otherPlayer) === 1) {
                $session->lastWinningPerson = array_shift($otherPlayer);
                $session->sendMessage(TextFormat::GREEN . $session->lastWinningPerson . " won the current sumo game against " . $player->getName());
            }

            $session->players[$player->getName()] = Session::PLAYER_STATE_SPECTATING;
        }

        $session->startRound();
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $session = $this->sumo->getSessionByPlayer($player);
        if (!$session) return;

        if ($session->players[$player->getName()] === Session::PLAYER_STATE_PLAYING) {
            $session->players[$player->getName()] = Session::PLAYER_STATE_SPECTATING;
            $otherPlayer = array_filter($this->sumo->getFightingPlayers($session), function ($v) use ($player) {
                return $v !== $player->getName();
            });

            if (sizeof($otherPlayer) === 1) {
                $session->lastWinningPerson = array_shift($otherPlayer);
                $session->sendMessage(TextFormat::RED . $player->getName() . " left the match!" . $session->lastWinningPerson . " is the winner");
            }  else {
                $session->lastWinningPerson = null;
                $session->sendMessage(TextFormat::RED . $player->getName() . " left the match! There are no winners");
            }
        } else {
            $session->players[$player->getName()] = Session::PLAYER_STATE_SPECTATING;
        }

        $session->startRound();
    }
}