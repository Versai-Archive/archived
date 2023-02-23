<?php


namespace Martin\GameAPI\Listener;


use Martin\GameAPI\Event\PlayerDeathEvent as APIPlayerDeathEvent;
use Martin\GameAPI\GamePlugin;
use Martin\GameAPI\Types\GameStateType;
use Martin\GameAPI\Types\PlayerStateType;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class PlayerListener implements Listener
{
    private GamePlugin $plugin;

    public function __construct(GamePlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onLevelChangeEvent(EntityLevelChangeEvent $event): void
    {
        $player = $event->getEntity();
        if (!$player instanceof Player) {
            return;
        }

        if ((($game = $this->getPlugin()->inGame($player)) !== null) && $game->getCurrentState() === GameStateType::STATE_ONGOING) {
            $event->setCancelled();
        }
    }

    /**
     * @return GamePlugin
     */
    public function getPlugin(): GamePlugin
    {
        return $this->plugin;
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if (($game = $this->getPlugin()->inGame($player)) !== null) {
            $game->removePlayer($player);
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        if (!is_null($game = $this->getPlugin()->inGame($player))) {
            $game_event = new APIPlayerDeathEvent($this->getPlugin(), $game, $event);
            $game_event->call();
        }
    }

    public function onEntityDamageByEntityEvent(EntityDamageByEntityEvent $event): void
    {
        $attacker = $event->getDamager();
        $taker = $event->getEntity();
        if (!($attacker instanceof Player) && !($taker instanceof Player)) {
            return;
        }

        if (!($game = $this->getPlugin()->inGame($attacker))) {
            return;
        }

        if (!($gameTaker = $this->getPlugin()->inGame($taker))) {
            return;
        }

        if ($game !== $gameTaker) {
            $event->setCancelled(true);
            return;
        }

        if (!in_array($attacker, $game->getPlayers(PlayerStateType::STATE_PLAYING), true) || !in_array($taker, $game->getPlayers(PlayerStateType::STATE_PLAYING), true)) {
            $event->setCancelled(true);
            return;
        }

        $event->setCancelled(false);
    }
}