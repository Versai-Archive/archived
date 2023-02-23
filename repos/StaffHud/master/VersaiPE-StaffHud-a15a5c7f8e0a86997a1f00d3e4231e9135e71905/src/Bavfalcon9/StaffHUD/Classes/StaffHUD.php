<?php

namespace Bavfalcon9\StaffHUD\Classes;

use Bavfalcon9\StaffHUD\Main;
use Bavfalcon9\StaffHUD\Math\Entities;
use Bavfalcon9\StaffHUD\Tasks\DelayedHotbar;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\math\VoxelRayTrace;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionParser;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\World;


class StaffHUD {
    public $items = [
        'compass' => '345:0:§r§7Compass',
        'unlock' => '280:0:§r§6Unlock §7from player',
        'lock' => '369:0:§r§6Lock §7to player',
        'inventory' => '340:0:§r§7View Inventory',
        'freeze' => '79:0:§r§bFreeze §7Player',
        'player' => '339:0:§r§7Player Info',
        'alerts' => '387:0:§r§4Alerts',
        'unvanished' => '351:8:§r§7You are §cunvanished',
        'vanished' => '351:10:§r§7You are §avanished'
    ];
    private $touchCool = [];
    private $enabled = [];
    private $cache = [];
    public $locked = [];
    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function disable(Player $player) {
        /* Forcefully disable a HUD for a user. */
        if ($player === null) return false;
        if (!isset($this->enabled[$player->getName()])) return false;
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $this->unvanishPlayer($player); // Forcefully unvanish user.
        unset($this->enabled[$player->getName()]);

        if (!isset($this->cache[$player->getName()])) {
            $player->setGamemode(GameMode::SURVIVAL());
            return true;
        } else {
            $data = $this->cache[$player->getName()];
            $player->getInventory()->setContents($data['inventory']);
            $player->setGamemode($data['gamemode']);
            unset($this->cache[$player->getName()]);
            return true;
        }
        
    }

    public function enable(Player $player) {
        $pk = new InventoryContentPacket();
        $pk->windowId = 121;
        $pk->items = [];
        $player->getNetworkSession()->sendDataPacket($pk);
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, true);
        $player->setNameTag('§c[V]§r ' . str_replace('§c[V]§r ', '', $player->getNameTag()));
        $this->cache[$player->getName()] = [
            'gamemode' => $player->getGamemode(),
            'inventory' => $player->getInventory()->getContents()
        ];
        $this->enabled[$player->getName()] = true;

        $player->setGamemode(GameMode::CREATIVE());
        $inventory = $player->getInventory();
        $inventory->clearAll();
        $inventory->setItem(0, $this->getItem($this->items['compass']));
        $inventory->setItem(1, $this->getItem($this->items['lock']));
        $inventory->setItem(3, $this->getItem($this->items['inventory']));
        $inventory->setItem(4, $this->getItem($this->items['freeze']));
        $inventory->setItem(5, $this->getItem($this->items['player']));
        $inventory->setItem(7, $this->getItem($this->items['alerts']));
        $inventory->setItem(8, $this->getItem($this->items['vanished']));

        foreach ($this->plugin->getServer()->getOnlinePlayers() as $pl) {
            if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                if (!$pl->hasPermission('StaffHUD.staffmode.see_op')) $pl->hidePlayer($player);
                continue;
            }
            if (!$pl->hasPermission('StaffHUD.staffmode.see')) $pl->hidePlayer($player);
            continue;
        }
        return true;
    }

    /**
     * fix the fucking hotbar, ugh idk how to fix ADAM!!!
     */
    public function hotbar(Player $player) {
        if (!$this->isEnabled($player)) return;
        else {
            if (isset($this->locked[$player->getName()])) unset($this->locked[$player->getName()]);
            $this->enable($player);
        }
    }

    public function vanishPlayer(Player $player) {
        if ($player->getNetworkProperties()->getAll()[EntityMetadataFlags::SILENT] === true) return true;
        $player->setNameTag('§c[V]§r ' . str_replace('§c[V]§r ', '', $player->getNameTag()));
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, true);
        $player->setGamemode(1);
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $pl) {
            if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                if (!$pl->hasPermission('StaffHUD.staffmode.see_op')) $pl->hidePlayer($player);
                continue;
            }
            if (!$pl->hasPermission('StaffHUD.staffmode.see')) $pl->hidePlayer($player);
            continue;
        }
        return true;
    }

    public function unvanishPlayer(Player $player) {
        if ($player->getNetworkProperties()->getAll()[EntityMetadataFlags::SILENT] === true) return true;
        $player->setNameTag(str_replace('§c[V]§r ', '', $player->getNameTag()));
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, false);
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $pl) {
            if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                if (!$pl->hasPermission('StaffHUD.staffmode.see_op')) $pl->showPlayer($player);
                continue;
            }
            if (!$pl->hasPermission('StaffHUD.staffmode.see')) $pl->showPlayer($player);
            continue;
        }
        return true;
    }

    /** EVENTS */
    public function handleClick($event) {
        $player = $event->getPlayer();
        if ($player->isClosed()) return;
        if (!isset($this->enabled[$player->getName()])) return;

        if (isset($this->touchCool[$player->getName()])) {
            if ($this->touchCool[$player->getName()] + 0.3 >=  microtime(true)) return;
        }

        $this->touchCool[$player->getName()] = microtime(true);
        $event->setCancelled();
        $inventory = $player->getInventory();
        $item = $inventory->getItemInHand();

        $compass = $this->getItem($this->items['compass']);
        $lock = $this->getItem($this->items['lock']);
        $unlock = $this->getItem($this->items['unlock']);
        $freeze = $this->getItem($this->items['freeze']);
        $vanished = $this->getItem($this->items['vanished']);
        $unvanished = $this->getItem($this->items['unvanished']);
        $alerts = $this->getItem($this->items['alerts']);

        if ($item->getId() === $compass->getId() && $compass->getDamage() === $compass->getDamage() && $compass->getCustomName() === $compass->getCustomName()) {
            // Teleport where player is looking.
            $tpTo = $this->getTpTo($player);
            if (!$tpTo) return;
            $player->teleport($tpTo);
        }

        if ($item->getId() === $freeze->getId() && $freeze->getDamage() === $freeze->getDamage() && $freeze->getCustomName() === $freeze->getCustomName()) {
            $ent = Entities::getNearestEntityLookingAt($player, 20);
            if (!$ent instanceof Player) return; 
            if (!$ent) {
                $player->sendPopup('§cLook at a player to freeze.');
                return;
            } else {
                if ($ent->isImmobile()) {
                    $player->sendPopup("§7Thawed §b{$ent->getName()}");
                    $ent->setImmobile(false);
                    return;
                } else {
                    $player->sendPopup("§7Froze §b{$ent->getName()}");
                    $ent->setImmobile(true);
                    return;
                }
            }
        }

        if ($item->getId() === $lock->getId() && $lock->getDamage() === $lock->getDamage() && $lock->getCustomName() === $lock->getCustomName()) {
            $ent = Entities::getNearestEntityLookingAt($player, 20);
            if (!$ent instanceof Player) return; 
            if (!$ent) {
                $player->sendPopup('§cLook at a player to lock.');
                return;
            }
            $this->locked[$player->getName()] = $ent->getName();
            $inventory->setItem(1, $unlock);
            $player->sendPopup($unlock->getCustomName() . " §f{$ent->getName()}");

            return;
        }
        if ($item->getId() === $unlock->getId() && $unlock->getDamage() === $unlock->getDamage() && $unlock->getCustomName() === $unlock->getCustomName()) {
            if (!isset($this->locked[$player->getName()])) return;
            $inventory->setItem(1, $lock);
            $player->sendPopup($lock->getCustomName() . " §f{$this->locked[$player->getName()]}");
            unset($this->locked[$player->getName()]);
            return;
        }

        if ($item->getId() === $alerts->getId() && $alerts->getDamage() === $alerts->getDamage() && $alerts->getCustomName() === $alerts->getCustomName()) {
            $ent = Entities::getNearestEntityLookingAt($player, 20);
            if (!$ent instanceof Player) return; 
            if (!$ent) {
                $item->setPageText(0, "§cInvalid player");
                $player->sendPopup('§cLook at a player to view alerts for.');
                $inventory->setItemInHand($item);
                $event->setCancelled(true);
                return;
            }
            $pages = $this->getMavoricViolations($ent);
            if (!$pages || empty($pages)) {
                $item->setPageText(0, "§aNo alerts for this user.");
                $inventory->setItemInHand($item);
                $player->sendPopup("§cNo alerts for this player.");
                $event->setCancelled(true);
                return;
            } else {
                $item->setPageText($pages[0], $pages[1]);
                $inventory->setItemInHand($item);
                $player->sendPopup("§4Alerts for §c{$ent->getName()}");
                return;
            }
        }

        if ($item->getId() === $vanished->getId() && $item->getDamage() === $vanished->getDamage() && $item->getCustomName() === $vanished->getCustomName()) {
            $inventory->setItem(8, $this->getItem($this->items['unvanished']));
            $player->sendPopup($unvanished->getCustomName());
            $this->unvanishPlayer($player);
            return;
        }
        if ($item->getId() === $unvanished->getId() && $item->getDamage() === $unvanished->getDamage() && $item->getCustomName() === $unvanished->getCustomName()) {
            $inventory->setItem(8, $this->getItem($this->items['vanished']));
            $player->sendPopup($vanished->getCustomName());
            $this->vanishPlayer($player);
            return;
        }
    }

    public function handleDrop($event) {
        $player = $event->getPlayer();
        if ($player->isClosed()) return;
        if (!isset($this->enabled[$player->getName()])) return;

        $event->setCancelled();
    }

    public function handleBreak($event) {
        $player = $event->getPlayer();
        if ($player->isClosed()) return;
        if (!isset($this->enabled[$player->getName()])) return;

        $event->setCancelled();
    }

    public function handlePlace($event) {
        $player = $event->getPlayer();
        if ($player->isClosed()) return;
        if (!isset($this->enabled[$player->getName()])) return;

        $event->setCancelled();
    }

    public function handleMove($event) {
        $player = $event->getPlayer();
        $locked = false;

        foreach ($this->locked as $mod=>$p) {
            if ($p === $player->getName()) {
                $mod = $this->plugin->getServer()->getPlayerByPrefix($mod);
                if (!$mod) continue;
                $distance = $this->getDistance($mod, $player);

                if ($distance >= 10) {
                    $mod->teleport($player->getPosition());
                    $mod->sendTip("§r§7Teleported to §flocked§7 player: §f{$p}§7.");
                }
            }
        }

    }

    public function handleDamage($event) {
        $ent = $event->getEntity();
        $dam = $event->getDamager();

        if (!$ent instanceof Player) return;
        if (!$dam instanceof Player) return;
        if (isset($this->enabled[$ent->getName()]) && ($ent->getNetworkProperties()->getAll()[EntityMetadataFlags::SILENT] === true)) {
            $this->fuckOffHotbar($ent, function ($ent, $hud) {
                $ent->setNameTag('§c[V]§r ' . str_replace('§c[V]§r ', '', $ent->getNameTag()));
            }, 5);
        }

        $player = $dam;
        if ($player->isClosed()) return;
        if (!isset($this->enabled[$player->getName()])) return;
        if (isset($this->touchCool[$player->getName()])) {
            if ($this->touchCool[$player->getName()] + 0.3 >=  microtime(true)) return;
        }
        $event->setCancelled(true);
        $this->touchCool[$player->getName()] = microtime(true);
        $inventory = $player->getInventory();
        $item = $inventory->getItemInHand();

        $compass = $this->getItem($this->items['compass']);
        $lock = $this->getItem($this->items['lock']);
        $unlock = $this->getItem($this->items['unlock']);
        $freeze = $this->getItem($this->items['freeze']);
        $vanished = $this->getItem($this->items['vanished']);
        $unvanished = $this->getItem($this->items['unvanished']);
        $alerts = $this->getItem($this->items['alerts']);

        if ($item->getId() === $lock->getId() && $lock->getDamage() === $lock->getDamage() && $lock->getCustomName() === $lock->getCustomName()) {
            $this->locked[$player->getName()] = $ent->getName();
            $inventory->setItem(1, $unlock);
            $player->sendPopup($unlock->getCustomName());
            return;
        }
        if ($item->getId() === $freeze->getId() && $freeze->getDamage() === $freeze->getDamage() && $freeze->getCustomName() === $freeze->getCustomName()) {
            if ($ent->isImmobile()) {
                $player->sendPopup("§7Thawed §b{$ent->getName()}");
                $ent->setImmobile(false);
                return;
            } else {
                $player->sendPopup("§7Froze §b{$ent->getName()}");
                $ent->setImmobile(true);
                return;
            }
        }
        if ($item->getId() === $alerts->getId() && $alerts->getDamage() === $alerts->getDamage() && $alerts->getCustomName() === $alerts->getCustomName()) {
            $ent = Entities::getNearestEntityLookingAt($player, 20);
            if (!$ent) {
                $item->setPageText(0, "§cInvalid player");
                $player->sendPopup('§cLook at a player to view alerts for.');
                $inventory->setItemInHand($item);
                $event->setCancelled(true);
                return;
            }
            $pages = $this->getMavoricViolations($ent);
            if (!$pages || empty($pages)) {
                $item->setPageText(0, "§aNo alerts for this user.");
                $inventory->setItemInHand($item);
                $player->sendPopup("§cNo alerts for this player.");
                $event->setCancelled(true);
                return;
            } else {
                $item->setPageText($pages[0], $pages[1]);
                $inventory->setItemInHand($item);
                $player->sendPopup("§4Alerts for §c{$ent->getName()}");
                return;
            }
        }
    }

    public function handleTeleport($event) {
        $player = $event->getEntity();
        $from = $event->getFrom();
        $to = $event->getTo();

        if (!$player instanceof Player) return;
        if (!isset($this->enabled[$player->getName()])) return;
        if ($from->getLevel()->getName() !== $to->getLevel()->getName()) {
            $this->fuckOffHotbar($player, function ($player, $HUD) {
                $inventory = $player->getInventory();
                $inventory->clearAll(true);
                $inventory->setItem(0, $HUD->getItem($HUD->items['compass']));
                $inventory->setItem(1, $HUD->getItem($HUD->items['lock']));
                $inventory->setItem(3, $HUD->getItem($HUD->items['inventory']));
                $inventory->setItem(4, $HUD->getItem($HUD->items['freeze']));
                $inventory->setItem(5, $HUD->getItem($HUD->items['player']));
                $inventory->setItem(7, $HUD->getItem($HUD->items['alerts']));
                $inventory->setItem(8, $HUD->getItem($HUD->items['vanished']));
                if (isset($HUD->locked[$player->getName()])) {
                    $inventory->setItem(1, $HUD->getItem($HUD->items['unlock']));
                }
            });
        }
    }

    public function fuckOffHotbar($player, $callBack, $time=10) {
        $hotbar = $this->plugin->getServer()->getPluginManager()->getPlugin('Hotbar');
        if (!$hotbar) return;
        $hotbar->getHotbarUsers()->remove($player);
        $task = new DelayedHotBar($player, $this, $callBack);
        $this->plugin->getScheduler()->scheduleDelayedTask($task, $time);
    }

    public function handleOpenInv($event) {
        $player = $event->getPlayer();
        if ($player->isClosed()) return;
        if (!isset($this->enabled[$player->getName()])) return;
        $event->setCancelled(true);
    }

    public function handleWriteBook($event) {
        $player = $event->getPlayer();
        if ($player->isClosed()) return;
        if (!isset($this->enabled[$player->getName()])) return;
        $event->setCancelled();
    }

    public function handleTransaction($event) {
        $player = $event->getTransaction()->getSource();
        if (!$player instanceof Player) return;
        if ($player->isClosed()) return;
        if (!isset($this->enabled[$player->getName()])) return;

        $event->setCancelled();
    }

    public function handleJoin($event) {
        $player = $event->getPlayer();
        if (!$player) return;

        foreach ($this->enabled as $p=>$time) {
            $pl = $this->plugin->getServer()->getPlayerbyPrefix($p);
            if (!$pl) {
                unset($this->enabled[$p]);
                continue;
            } else {
                if ($pl->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    if (!$player->hasPermission('StaffHUD.staffmode.see_op')) $player->hidePlayer($pl);
                    continue;
                }
                if (!$player->hasPermission('StaffHUD.staffmode.see')) $player->hidePlayer($pl);
            }
        }
    }

    public function handleQuit($event) {
        $player = $event->getPlayer();
        if ($this->isEnabled($player)) unset($this->enabled[$player->getName()]);
    }

    public function getItem(String $itemStr): ?Item {
        $str = explode(':', $itemStr);
        $id = $str[0];
        $meta = $str[1];
        $name = $str[2];

       $item = ItemFactory::getInstance()->get($id, $meta);

        $item->setCustomName($name);
        return $item;
    }

    public function isEnabled($player): ?Bool {
        if ($player instanceof Player) $player = $player->getName();
        return (isset($this->enabled[$player]));
    }

    public function getTpTo(Player $player): ?Vector3 {
        /**
         * Credit: TapTp @Dylan
         * GitHub: <>
         */
        $start = $player->getPosition()->add(0, $player->getEyeHeight(), 0);

        $end  = $start->add($player->getViewDistance() * 16, 0, 0);


        $level = $player->getWorld();
        foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3){
            if($vector3->y >= World::Y_MAX or $vector3->y <= 0){
                return null;
            }
            if(!$level->isChunkLoaded($vector3->x >> 4, $vector3->z >> 4) or !$level->getChunk($vector3->x >> 4, $vector3->z >> 4)->isPopulated()){
                return null;
            }
            if(($result = $level->getBlockAt($vector3->x, $vector3->y, $vector3->z)->calculateIntercept($start, $end)) !== null){
                if ($level->getBlockAt($vector3->x, $vector3->y, $vector3->z)->isTransparent()) continue;
                $target = $result->hitVector;
                return $target;
            }
        }
        return null;
    }
   /* public function rayTraceEntities(Player $player): ?Vector3 {
        $start = $player->add(0, $player->getEyeHeight(), 0);
        $end = $start->add($player->getDirectionVector()->multiply($player->getViewDistance() * 16));
        $level = $player->level;
        foreach($level->getEntities() as $ent) {

        }
    } */

    public function getDistance($dam, $p) {
        $dx = $dam->getX();
        $dy = $dam->getY();
        $dz = $dam->getZ();
        $px = $p->getX();
        $py = $p->getY();
        $pz = $p->getZ();

        $distanceX = sqrt(pow(($px - $dx), 2) + pow(($py - $dy), 2));
        $distanceZ = sqrt(pow(($pz - $dz), 2) + pow(($py - $dy), 2));
        return (abs($distanceX) > abs($distanceZ)) ? abs($distanceX) : abs($distanceZ);
    }

    private function getMavoricViolations($entity): ?array
    {
        $mavoric = $this->plugin->getServer()->getPluginManager()->getPlugin('Mavoric');
        
        if (!$mavoric) return [[0, '§cMavoric not installed on this server!']];
        else {
            if (!$entity instanceof Player) return null;

            $flag = $mavoric->mavoric->getFlag($entity);
            $data = $flag->getFlagsByNameAndCount();
            $count = 0;
            $complete = [];
            if (empty($data)) return null;

            foreach ($data as $cheat=>$amount) {
                if ($count === 0) array_push($complete, "==§0Alert : Count==");
                array_push($complete, "§4{$cheat}§0: §c{$amount}");
                $count++;
            }

            return (empty($complete)) ? [] : [0, implode("\n", $complete)];
        }
    }
}