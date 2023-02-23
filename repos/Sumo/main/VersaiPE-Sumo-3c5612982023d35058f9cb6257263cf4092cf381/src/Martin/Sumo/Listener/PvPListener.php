<?php


namespace Martin\Sumo\Listener;


use Martin\GameAPI\Event\PlayerDeathEvent as APIPlayerDeathEvent;
use Martin\GameAPI\Types\GameStateType;
use Martin\GameAPI\Types\PlayerStateType;
use Martin\Sumo\Game\Sumo;
use Martin\Sumo\Main;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class PvPListener implements Listener
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        if ((($game = $this->plugin->getGameByPlayer($event->getPlayer())) === null)) {
            return;
        }

        if (!$game instanceof Sumo):
            return;
        endif;

        $name = $event->getPlayer()->getLowerCaseName();
        if (!in_array($name, [$game->getPlayerRandomlyChoosen(), $game->getPlayerWonLastRound()], true)) {
            return;
        }

        (new APIPlayerDeathEvent($this->plugin, $game, $event))->call();
    }

    public function onDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();
        if (!($player instanceof Player)) {
            return;
        }

        if (($game = $this->plugin->getGameByPlayer($player)) === null || !($game instanceof Sumo)) {
            return;
        }

        if ($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
            $event->setCancelled(true);
            $game->onDeath(new APIPlayerDeathEvent($this->plugin, $game, $event));
        }
    }

    public function onEntityByEntityEvent(EntityDamageByEntityEvent $event): void
    {
        $attacker = $event->getDamager();
        $taker = $event->getEntity();
        if (!($attacker instanceof Player) || !($taker instanceof Player)) {
            return;
        }

        if (($game = $this->plugin->getGameByPlayer($attacker)) === null || !($game instanceof Sumo)) {
            return;
        }

        if (!in_array($attacker, $game->getPlayers(PlayerStateType::STATE_PLAYING), true)) {
            $event->setCancelled();
        } else if (!in_array($taker, $game->getPlayers(), true)) { # Player/DamagerTaker not inside and fighting
            $event->setCancelled();
        } else {
            $event->setCancelled(false);
        }
    }


    public function onLevelChangeEvent(EntityLevelChangeEvent $event): void
    {
        $player = $event->getEntity();
        if (!$player instanceof Player) {
            return;
        }

        if ((($game = $this->plugin->inGame($player)) !== null) && $game->getCurrentState() === GameStateType::STATE_ONGOING) {
            $event->setCancelled();
        }
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if (($game = $this->getPlugin()->inGame($player)) !== null) {
            $game->removePlayer($player);
        }
    }

    public function getPlugin(): Main
    {
        return $this->plugin;
    }
}