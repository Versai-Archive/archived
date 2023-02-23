<?php
declare(strict_types=1);

namespace Versai\arenas\event;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;
use Versai\arenas\Arena;
use Versai\arenas\Arenas;
use Versai\arenas\player\ArenaPlayer;

class ArenaListener implements Listener{

    private Arenas $plugin;

    public function __construct(Arenas $plugin){
        $this->plugin = $plugin;
    }

    public function onDamage(EntityDamageEvent $event): void{
        $entity = $event->getEntity();
        $levelName = $entity->getWorld()->getDisplayName();
        if(isset($this->plugin->arenas[$levelName])){
            /** @var Arena $arena */
            $arena = $this->plugin->arenas[$levelName];
            if($event instanceof EntityDamageByEntityEvent && $entity instanceof Player){

                if($this->distance($arena->getSpawnLocation(), $entity->getLocation()) < $arena->getProtectionArea()){
                    $event->cancel();
                }
                $event->setKnockBack($arena->getKnockback());
                $event->setAttackCooldown($arena->getHitCooldown());
            } elseif($event->getCause() === EntityDamageEvent::CAUSE_FALL && !$arena->hasFallDamage()){
                $event->cancel();
            }
        }
    }

    public function onBreak(BlockBreakEvent $event): void{
        $player = $event->getPlayer();
        $levelName = $player->getWorld()->getDisplayName();
        $block = $event->getBlock();
        if($player instanceof Player && isset($this->plugin->arenas[$levelName])){
            /** @var Arena $arena */
            $arena = $this->plugin->arenas[$levelName];
            if(!($this->distance($arena->getSpawnLocation(), $block->getPos()) < $arena->getProtectionArea())){
                if ($arena->isBreakable()){
                    $blockID = $event->getBlock()->getId();
                    if (!in_array($blockID, $arena->getAllowedBlocksList())){
                        $event->cancel();
                    }
                } else {
                    $event->cancel();
                }
            }else{
                $event->cancel();
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event): void{
        $player = $event->getPlayer();
        $levelName = $player->getWorld()->getDisplayName();
        $block = $event->getBlock();
        if($player instanceof Player && isset($this->plugin->arenas[$levelName])){
            /** @var Arena $arena */
            $arena = $this->plugin->arenas[$levelName];
            if(!($this->distance($arena->getSpawnLocation(), $block->getPos()) < $arena->getProtectionArea())){
                if ($arena->isPlaceable()) {
                    $blockID = $event->getBlock()->getId();
                    if (!in_array($blockID, $arena->getAllowedBlocksList())) {
                        $event->cancel();
                    }
                    if($arena->hasBlockDecay()){
                        $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(
                            function () use ($block): void {
                                $block->getPos()->getWorld()->setBlock($block->getPos()->asVector3(), VanillaBlocks::AIR());
                            }
                        ), 20 * 15);
                    }
                } else {
                    $event->cancel();
                }
            }else{
                $event->cancel();
            }
        }
    }

    public function onExhaust(PlayerExhaustEvent $event): void{
        $player = $event->getPlayer();
        $levelName = $player->getWorld()->getDisplayName();
        if($player instanceof Player && !isset($this->plugin->arenas[$levelName])){
            /** @var Arena $arena */
            $arena = $this->plugin->arenas[$levelName];
            if(!$arena->hasHungerLoss()){
                $player->getHungerManager()->setFood(20);
                $event->cancel();
            }
        }
    }

    public function onPickup(InventoryPickupItemEvent $event): void{
        $levelName = $event->getItemEntity()->getWorld()->getDisplayName();
        if(isset($this->plugin->arenas[$levelName])){
            /** @var Arena $arena */
            $arena = $this->plugin->arenas[$levelName];
            if(!$arena->canPickUpItems()){
                $event->cancel();
            }
        }
    }

    public function onCreation(PlayerCreationEvent $event): void{
        $event->setPlayerClass(ArenaPlayer::class);
    }

    public function distance(Position $pos1, Vector3 $pos2): float{
        return sqrt($this->distanceSquared($pos1, $pos2));
    }

    public function distanceSquared(Position $pos1, Vector3 $pos2) : float{
        return (($pos1->x - $pos2->x) ** 2) + (($pos1->z - $pos2->z) ** 2);
    }
}