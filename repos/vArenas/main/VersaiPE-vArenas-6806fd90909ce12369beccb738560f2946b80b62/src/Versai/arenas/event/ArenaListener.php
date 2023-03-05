<?php
declare(strict_types=1);

namespace Versai\arenas\event;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Position;
use Versai\arenas\Arena;
use Versai\arenas\ArenaPlayer;
use Versai\arenas\Arenas;
use function sqrt;

class ArenaListener implements Listener
{

    private Arenas $plugin;

    public function __construct(Arenas $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        $levelName = $entity->getWorld()->getFolderName();
        if (!isset($this->plugin->arenas[$levelName])) return;

        /** @var Arena $arena */
        $arena = $this->plugin->arenas[$levelName];
        if ($event instanceof EntityDamageByEntityEvent && $entity instanceof Player) {
            if ($entity->getPosition()->distance($arena->getSpawnLocation()) < $arena->getProtectionArea()) {
                $event->cancel();
            }
            $event->setKnockBack($arena->getKnockback());
            $event->setAttackCooldown($arena->getHitCooldown());
        } elseif ($event->getCause() === EntityDamageEvent::CAUSE_FALL && !$arena->hasFallDamage()) {
            $event->cancel();
        }
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $levelName = $player->getWorld()->getFolderName();
        $block = $event->getBlock();
        if (!isset($this->plugin->arenas[$levelName]) || !$player instanceof Player) return;

        /** @var Arena $arena */
        $arena = $this->plugin->arenas[$levelName];
        if ($arena->isBreakable()) {
            $blockID = $block->getId();
            if (!in_array($blockID, $arena->getAllowedBlocksList())) {
                $event->cancel();
            }
        } else {
            $event->cancel();
        }

    }

    public function onPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $levelName = $player->getWorld()->getFolderName();
        if (!isset($this->plugin->arenas[$levelName]) || !$player instanceof Player) return;

        /** @var Arena $arena */
        $arena = $this->plugin->arenas[$levelName];
        if ($arena->isPlaceable()) {
            $blockID = $event->getBlock()->getId();
            if (!in_array($blockID, $arena->getAllowedBlocksList())) {
                $event->cancel();
            }
        } else {
            $event->cancel();
        }
    }

    public function onExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        $levelName = $player->getWorld()->getFolderName();
        if (!isset($this->plugin->arenas[$levelName]) || !$player instanceof Player) return;


        /** @var Arena $arena */
        $arena = $this->plugin->arenas[$levelName];
        if (!$arena->hasHungerLoss()) {
            $player->getHungerManager()->setFood(20);
            $event->cancel();
        }

    }

    public function onPickup(EntityItemPickupEvent $event): void
    {
        $levelName = $event->getOrigin()->getWorld()->getFolderName();
        if (!isset($this->plugin->arenas[$levelName])) return;

        /** @var Arena $arena */
        $arena = $this->plugin->arenas[$levelName];
        if (!$arena->canPickUpItems()) {
            $event->cancel();
        }
    }

    public function onCreation(PlayerCreationEvent $event)
    {
        $event->setPlayerClass(ArenaPlayer::class);
    }

    public function blockDistance(Position $pos1, Vector3 $pos2): float
    {
        return sqrt($this->blockDistanceSquared($pos1, $pos2));
    }

    public function blockDistanceSquared(Position $pos1, Vector3 $pos2): float
    {
        return (($pos1->x - $pos2->x) ** 2) + (($pos1->z - $pos2->z) ** 2);
    }

    public function attackDistance(Position $pos1, Vector3 $pos2): float
    {
        return sqrt($this->attackDistanceSquared($pos1, $pos2));
    }

    public function attackDistanceSquared(Position $pos1, Vector3 $pos2): float
    {
        return (($pos1->x - $pos2->x) ** 2) + (($pos1->y - $pos2->y) ** 1) + (($pos1->z - $pos2->z) ** 2);
    }
}