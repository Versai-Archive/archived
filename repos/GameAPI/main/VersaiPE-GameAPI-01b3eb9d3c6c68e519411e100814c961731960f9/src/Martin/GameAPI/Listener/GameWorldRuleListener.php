<?php

namespace Martin\GameAPI\Listener;

use Martin\GameAPI\Game\Game;
use Martin\GameAPI\GamePlugin;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\Player;


class GameWorldRuleListener implements Listener
{
    public const NATURAL_REGAIN_REASONS = [EntityRegainHealthEvent::CAUSE_REGEN, EntityRegainHealthEvent::CAUSE_EATING, EntityRegainHealthEvent::CAUSE_SATURATION];

    private GamePlugin $plugin;
    /** @var Block[] */
    private array $blocksBuildByPlayers = [];

    public function __construct(GamePlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        if ($game = $this->getGameByPlayer($event->getPlayer())) {
            if ($game->getGameRules()->canBuild()) {
                $this->blocksBuildByPlayers[] = $event->getBlock();
            } else {
                $event->setCancelled();
            }
        }
    }

    private function getGameByPlayer(Player $player): ?Game
    {
        return $this->plugin->inGame($player);
    }

    public function onBlockBreak(BlockBreakEvent $event): void
    {
        if ($game = $this->getGameByPlayer($event->getPlayer())) {
            if ($game->getGameRules()->canBreak() === false) {
                if ($game->getGameRules()->canBreakFromPlayers()) {
                    if (!in_array($event->getBlock(), $this->blocksBuildByPlayers)) {
                        $event->setCancelled();
                    }
                } else {
                    $event->setCancelled();
                }
            }
        }
    }

    public function onDamageNonEntity(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();
        if (!($player instanceof Player)) return;
        if ($game = $this->getGameByPlayer($player)) {
            if ($game->getGameRules()->canTakeFallDamage()) {
                if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                    $event->setCancelled();
                }
            }

            if ($game->getGameRules()->canTakeDrowningDamage()) {
                if ($event->getCause() === EntityDamageEvent::CAUSE_DROWNING) {
                    $event->setCancelled();
                }
            }
        }
    }

    public function onExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        if (!($player instanceof Player)) return;
        if ($game = $this->getGameByPlayer($player)) {
            if (!$game->getGameRules()->canTakeHunger()) {
                $event->setCancelled();
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        if ($game = $this->getGameByPlayer($event->getPlayer())) {
            if ($game->getGameRules()->doItemsDrop() === false) {
                $event->setDrops([]);
            }

            if ($game->getGameRules()->doXpDrop() === false) {
                $event->setXpDropAmount(0);
            }

            $event->setKeepInventory($game->getGameRules()->keepInventory());
        }
    }

    public function onDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if (!$damager) {
            return;
        }

        if (!($player instanceof Player) || !($damager instanceof Player)) {
            return;
        }

        if ($game = $this->getGameByPlayer($player)) {
            if (!$game->getGameRules()->canTakeHunger()) {
                $event->setCancelled();
            }
        }
    }

    public function onRegeneration(EntityRegainHealthEvent $event): void
    {
        $player = $event->getEntity();
        if (!($player instanceof Player)) return;
        if ($game = $this->getGameByPlayer($player)) {
            if (!$game->getGameRules()->canRegenNatural()) {
                if (in_array($event->getRegainReason(), self::NATURAL_REGAIN_REASONS, true)) {
                    $event->setCancelled();
                }
            }

            if (!$game->getGameRules()->canRegenNonNatural()) {

            }
        }
    }

    public function onPickup(InventoryPickupItemEvent $event): void
    {
        $viewers = $event->getViewers();
        foreach ($viewers as $viewer) {
            if ($viewer instanceof Player) {
                if ($game = $this->plugin->inGame($viewer)) {
                    if (!$game->getGameRules()->canPickUp()) {
                        $event->setCancelled();
                    }
                }
            }
        }
    }

}