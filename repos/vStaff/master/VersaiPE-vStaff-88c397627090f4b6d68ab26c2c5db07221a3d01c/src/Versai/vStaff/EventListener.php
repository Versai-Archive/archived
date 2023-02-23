<?php
declare(strict_types=1);

namespace Versai\vStaff;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\transaction\action\CreateItemAction;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use ReflectionException;
use Versai\Hotbar\Main as HotbarMain;
use Versai\vStaff\libs\jojoe77777\FormAPI\SimpleForm;
use Versai\vStaff\math\Entities;
use Versai\vStaff\tasks\CPSHandlerTask;
use Versai\vStaff\tasks\DelayedHotBar;
use function microtime;
use function str_replace;

class EventListener implements Listener {

    const TAG_COMPASS = 'VS_COMPASS';
    const TAG_LOCK = 'VS_LOCK';
    const TAG_UNLOCK = 'VS_UNLOCK';
    const TAG_VANISH = 'VS_VANISH';
    const TAG_UNVANISH = 'VS_UNVANISH';
    const TAG_CPS = 'VS_CPS';
    const TAG_FREEZE = 'VS_FREEZE';
    const TAG_PINFO = 'VS_PINFO';
    const TAG_PUNISH = 'VS_PUNISH';
    const TAG_TRUE_NAME = 'VS_TRUENAME';

	private Main $plugin;

    public array $locked = [];
    private static array $enabled = [];
    private static array $cache = [];
    private array $touchCool = [];
    private array $cpsCooldown = [];
    private array $tap2punish = [];

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	public function onDrop(PlayerDropItemEvent $event) {
        $player = $event->getPlayer();

        if($player->isClosed()) return;
        if(!isset($this->enabled[$player->getName()])) return;

        $event->cancel();
	}

	public function onTransaction(InventoryTransactionEvent $event) {
        $player = $event->getTransaction()->getSource();

        if(!$player instanceof Player || $player->isClosed()) return;
        if(!isset(self::$enabled[$player->getName()])) return;

        foreach($event->getTransaction()->getActions() as $action){
            if($action instanceof CreateItemAction){
                $event->cancel();
            }
        }
	}

	public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        if(!$player) return;

        foreach(self::$enabled as $p => $bool) {
            $pl = $this->plugin->getServer()->getPlayerByPrefix($p);
            if(!$pl) {
                unset(self::$enabled[$p]);
                continue;
            } else {
                if($pl->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    if(!$player->hasPermission('staffhud.staffmode.see_op')) {
                        $player->hidePlayer($pl);
                    }
                    continue;
                }
                if(!$player->hasPermission('staffhud.staffmode.see')) {
                    $player->hidePlayer($pl);
                }
            }
        }
	}

	public function onLeave(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        if($this->isEnabled($player)) {
            unset(self::$enabled[$player->getName()]);
        }
        if(isset($this->locked[$player->getName()])) {
            unset($this->locked[$player->getName()]);
        }
	}

	public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        if($player->isClosed()) return;
        if(!isset(self::$enabled[$player->getName()])) return;

        $event->cancel();
	}

	public function onPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        if($player->isClosed()) return;
        if(!isset(self::$enabled[$player->getName()])) return;

        $event->cancel();
	}

	public function onMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        foreach($this->locked as $mod => $p) {
            if($p === $player->getName()) {
                $mod = $this->plugin->getServer()->getPlayerByPrefix($mod);
                if(!$mod) continue;
                $distance = Entities::getDistance($mod, $player);

                if($distance >= 10) {
                    $mod->teleport(new Position($player->getPosition()->x, $player->getPosition()->y, $player->getPosition()->z, $player->getWorld()));
                    $mod->sendTip("§r§7Teleported to §flocked§7 player: §f{$p}§7.");
                }
            }
        }
	}

	public function onUse(PlayerItemUseEvent $event) {
        $player = $event->getPlayer();

        if ($player->isClosed()) return;
        if (!isset(self::$enabled[$player->getName()])) return;
        if (isset($this->touchCool[$player->getName()])) {
            if ($this->touchCool[$player->getName()] + 0.3 >= microtime(true)) return;
        }

        $this->touchCool[$player->getName()] = microtime(true);

        $inventory = $player->getInventory();
        $item = $inventory->getItemInHand();
        $lock = self::getItem('lock');
        $unlock = self::getItem('unlock');
        $vanished = self::getItem('vanished');
        $unvanished = self::getItem('unvanished');

        $itemNamedTag = $item->getNamedTag();

        if ($itemNamedTag->getTag(self::TAG_COMPASS) !== null) {
            // Teleport where player is looking.
            $tpTo = $this->getTpTo($player);
            if (!$tpTo) return;
            $player->teleport($tpTo);
            return;
        }
        if($itemNamedTag->getTag(self::TAG_UNLOCK) !== null){
            if(!isset($this->locked[$player->getName()])) return;
            $inventory->setItem(1, $lock);
            $player->sendPopup($unlock->getCustomName() . " §f{$this->locked[$player->getName()]}");
            unset($this->locked[$player->getName()]);
            return;
        }
        if($itemNamedTag->getTag(self::TAG_VANISH) !== null){
            $inventory->setItem(8, $unvanished);
            $player->sendPopup($unvanished->getCustomName());
            $this->unvanishPlayer($player);
            return;
        }
        if($itemNamedTag->getTag(self::TAG_UNVANISH) !== null){
            $inventory->setItem(8, $vanished);
            $player->sendPopup($vanished->getCustomName());
            $this->vanishPlayer($player);
            return;
        }
	}

    /**
     * @param EntityDamageByEntityEvent $event
     * @priority HIGHEST
     * @handleCancelled 
     * @throws ReflectionException
     */
	public function onDamage(EntityDamageEvent $event) {
        if($event instanceof EntityDamageByEntityEvent) {
            $ent = $event->getEntity();
            $dam = $event->getDamager();

            if (!($ent instanceof Player) || !($dam instanceof Player)) return;
            if ($dam->isClosed() || $ent->isClosed()) return;
            if (!isset(self::$enabled[$dam->getName()])) return;

            if (isset(self::$enabled[$ent->getName()])) {
                $this->fuckOffHotbar($ent, function ($player, $HUD) {
                    $player->setNameTag('§c[V]§r ' . str_replace('§c[V]§r ', '', $player->getNameTag()));
                    $inventory = $player->getInventory();
                    $inventory->clearAll();
                    $inventory->setItem(0, self::getItem('compass'));
                    $inventory->setItem(1, self::getItem('lock'));
                    $inventory->setItem(2, self::getItem('cps'));
                    $inventory->setItem(3, self::getItem('freeze'));
                    $inventory->setItem(5, self::getItem('pinfo'));
                    $inventory->setItem(6, self::getItem('punish'));
                    $inventory->setItem(7, self::getItem('name'));
                    $inventory->setItem(8, self::getItem('vanished'));
                    if (isset($HUD->locked[$player->getName()])) {
                        $inventory->setItem(1, self::getItem('unlock'));
                    }
                });
            }

            if (isset($this->touchCool[$dam->getName()])) {
                if ($this->touchCool[$dam->getName()] + 0.3 >= microtime(true)) {
                    return;
                }
            }
            $this->touchCool[$dam->getName()] = microtime(true);
            $inventory = $dam->getInventory();
            $item = $inventory->getItemInHand();

            $lock = self::getItem('lock');
            $unlock = self::getItem('unlock');

            $itemNamedTag = $item->getNamedTag();

            if ($itemNamedTag->getTag(self::TAG_LOCK) !== null) {
                $this->locked[$dam->getName()] = $ent->getName();
                $inventory->setItem(1, $unlock);
                $dam->sendPopup($unlock->getCustomName());
                return;
            } 
            
            if ($itemNamedTag->getTag(self::TAG_UNLOCK) !== null) {
                unset($this->locked[$dam->getName()]);
                $inventory->setItem(1, $lock);
                return;
            } 
            
            if ($itemNamedTag->getTag(self::TAG_FREEZE) !== null) {
                if ($ent->isImmobile()) {
                    $dam->sendPopup("§7Thawed §b{$ent->getName()}");
                    $ent->setImmobile(false);
                } else {
                    $dam->sendPopup("§7Froze §b{$ent->getName()}");
                    $ent->setImmobile(true);
                }
                return;
            } 
            
            if ($itemNamedTag->getTag(self::TAG_PINFO) !== null) {
                $this->plugin->getServer()->dispatchCommand($dam, "pinfo " . $ent->getName());
                return;
            } 
            
            if ($itemNamedTag->getTag(self::TAG_TRUE_NAME) !== null) {
                $dam->sendPopup($ent->getName());
                return;
            } 
            
            if ($itemNamedTag->getTag(self::TAG_PUNISH) !== null) {
                $this->tap2punish[$dam->getName()] = $ent->getName();

                $closure = function (Player $player, $data): void {
                    if ($data !== null) {
                        switch ($data) {
                            case 0:
                                $this->plugin->getServer()->dispatchCommand($player, "mute " . '"' . $this->tap2punish[$player->getName()] . '"');
                                unset($this->tap2punish[$player->getName()]);
                                break;
                            case 1:
                                $this->plugin->getServer()->dispatchCommand($player, "kick " . '"' . $this->tap2punish[$player->getName()] . '"');
                                unset($this->tap2punish[$player->getName()]);
                                break;
                            case 2:
                                $this->plugin->getServer()->dispatchCommand($player, "ban " . '"' . $this->tap2punish[$player->getName()] . '"');
                                unset($this->tap2punish[$player->getName()]);
                                break;
                            default:
                                break;
                        }
                    }
                };

                $form = new SimpleForm($closure);
                $form->setTitle("Choose Action");
                $form->setContent("Choose an option");
                $form->addButton("§bMute");
                $form->addButton("§eKick");
                $form->addButton("§aBan");
                $form->addButton("§cCancel");

                $dam->sendForm($form);
                return;
            }
            
            if ($itemNamedTag->getTag(self::TAG_CPS) !== null) {
                if (isset($this->cpsCooldown[$dam->getName()]) && $this->cpsCooldown[$dam->getName()] + 6 >= microtime(true)) {
                    $dam->sendMessage(TextFormat::RED . "Woah! Slowdown there.");
                } else {
                    $this->cpsCooldown[$dam->getName()] = microtime(true);
                    $this->plugin->cps[$ent->getName()] = 0;
                    $this->plugin->getScheduler()->scheduleDelayedTask(new CPSHandlerTask($this->plugin, $dam, $ent->getName()), 20 * 5);
                    $dam->sendMessage(TextFormat::GREEN . "Started counting " . $ent->getName() . "'s cps");
                }
            }

            $event->cancel();
        }
	}

    /**
     * @throws ReflectionException
     */
    public function onTeleport(EntityTeleportEvent $event) {
        $player = $event->getEntity();
        $from = $event->getFrom();
        $to = $event->getTo();

        if(!$player instanceof Player) return;
        if(!isset(self::$enabled[$player->getName()])) return;

        if($from->getWorld()->getDisplayName() !== $to->getWorld()->getDisplayName()) {
            $this->fuckOffHotbar($player, function($player, $HUD) {
                $inventory = $player->getInventory();
                $inventory->clearAll();
                $inventory->setItem(0, self::getItem('compass'));
                $inventory->setItem(1, self::getItem('lock'));
                $inventory->setItem(2, self::getItem('cps'));
                $inventory->setItem(3, self::getItem('freeze'));
                $inventory->setItem(5, self::getItem('pinfo'));
                $inventory->setItem(6, self::getItem('punish'));
                $inventory->setItem(7, self::getItem('name'));
                $inventory->setItem(8, self::getItem('vanished'));
                $this->vanishPlayer($player);

                if(isset($HUD->locked[$player->getName()])) {
                    $inventory->setItem(1, self::getItem('unlock'));
                }
            });

            foreach($this->locked as $mod => $e) {
                if($e == $player->getName()){
                    $this->plugin->getServer()->getPlayerByPrefix($mod)->teleport($player->getPosition());
                }
            }
        }
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) {
		if(($player = $event->getOrigin()->getPlayer()) !== null) {
            $p = $event->getPacket();
            if ($p instanceof LevelSoundEventPacket and $p->sound == LevelSoundEvent::ATTACK_NODAMAGE or $p instanceof InventoryTransactionPacket and $p->trData->getTypeId() === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) {
                if (isset($this->plugin->cps[$player->getName()])) {
                    $this->plugin->addClick($player);
                }
            }
        }
	}


	public static function enableStaffMode(Player $player){
        $pk = new InventoryContentPacket();
        $pk->windowId = 121;
        $pk->items = [];
        $player->getNetworkSession()->sendDataPacket($pk);

        self::vanishPlayer($player);

        self::$cache[$player->getName()] = [
            'inventory' => $player->getInventory()->getContents(),
            'armor' => $player->getArmorInventory()->getContents()
        ];
        self::$enabled[$player->getName()] = true;

        $player->getArmorInventory()->clearAll();
        $inventory = $player->getInventory();
        $inventory->clearAll();
        $inventory->setItem(0, self::getItem('compass'));
        $inventory->setItem(1, self::getItem('lock'));
        $inventory->setItem(2, self::getItem('cps'));
        $inventory->setItem(3, self::getItem('freeze'));
        $inventory->setItem(5, self::getItem('pinfo'));
        $inventory->setItem(6, self::getItem('punish'));
        $inventory->setItem(7, self::getItem('name'));
        $inventory->setItem(8, self::getItem('vanished'));

        return true;
    }

    public static function disableStaffMode(Player $player): bool{
        /* Forcefully disable a HUD for a user. */
        if(!isset(self::$enabled[$player->getName()])) return false;

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        self::unvanishPlayer($player); // Forcefully unvanish user.
        unset(self::$enabled[$player->getName()]);
        $player->setGamemode(GameMode::SURVIVAL());
        if(isset(self::$cache[$player->getName()])) {
            $data = self::$cache[$player->getName()];
            $player->getInventory()->setContents($data['inventory']);
            $player->getArmorInventory()->setContents($data['armor']);
            unset(self::$cache[$player->getName()]);
        }
        return true;
    }

    public function getTpTo(Player $player) : ?Vector3{
        /** Credit: AimTP @dktapps */
        $start = $player->getPosition()->add(0, $player->getEyeHeight(), 0);
        $dirVec = $player->getDirectionVector()->multiply($player->getViewDistance() * 16);
        $end = $start->add($dirVec->getX(), $dirVec->getY(), $dirVec->getZ());
        $level = $player->getWorld();
        foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3){
            if($vector3->y >= World::Y_MAX or $vector3->y <= 0) return null;
            if(!$level->isChunkLoaded($vector3->x >> 4, $vector3->z >> 4)) return null;

            if(($result = $level->getBlockAt($vector3->x, $vector3->y, $vector3->z)->calculateIntercept($start, $end)) !== null){
                if($level->getBlockAt($vector3->x, $vector3->y, $vector3->z)->isTransparent()) continue;
                return $result->hitVector;
            }
        }
        return null;
    }

    public static function vanishPlayer(Player $player) {
        $player->setNameTag('§c[V]§r ' . str_replace('§c[V]§r ', '', $player->getNameTag()));
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, true);
        $player->setGamemode(GameMode::CREATIVE());
        foreach(Server::getInstance()->getOnlinePlayers() as $pl) {
            if($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                if(!$pl->hasPermission('staffhud.staffmode.see_op')) {
                    $pl->hidePlayer($player);
                }
                continue;
            }
            if(!$pl->hasPermission('staffhud.staffmode.see')) {
                $pl->hidePlayer($player);
            }
            continue;
        }
        return true;
    }

    private static function unvanishPlayer(Player $player) : void{
        $player->setNameTag(str_replace('§c[V]§r ', '', $player->getNameTag()));
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, false);
        foreach(Server::getInstance()->getOnlinePlayers() as $pl) {
            $pl->showPlayer($player);
        }
    }

    public static function getItem(string $itemStr) : ?Item {
        switch($itemStr){
            case 'compass':
                $compass = VanillaItems::COMPASS()
                    ->setCustomName('§r§7Compass');
                $compass->getNamedTag()->setInt(self::TAG_COMPASS, 1);
                return $compass;
            case 'cps':
                $book = VanillaItems::BOOK()
                    ->setCustomName('§r§7Check CPS');
                $book->getNamedTag()->setInt(self::TAG_CPS, 1);
                return $book;
            case 'unlock':
                $stick = VanillaItems::STICK()
                    ->setCustomName('§r§6Unlock §7from player');
                $stick->getNamedTag()->setInt(self::TAG_UNLOCK, 1);
                return $stick;
            case 'lock':
                $blaze_rod = VanillaItems::BLAZE_ROD()
                    ->setCustomName('§r§6Lock §7to player');
                $blaze_rod->getNamedTag()->setInt(self::TAG_LOCK, 1);
                return $blaze_rod;
            case 'freeze':
                $ice = VanillaBlocks::ICE()->asItem()
                    ->setCustomName('§r§bFreeze §7Player');
                $ice->getNamedTag()->setInt(self::TAG_FREEZE, 1);
                return $ice;
            case 'pinfo':
                $paper = VanillaItems::PAPER()
                    ->setCustomName('§r§7Player Info');
                $paper->getNamedTag()->setInt(self::TAG_PINFO, 1);
                return $paper;
            case 'punish':
                $clock = VanillaItems::CLOCK()
                    ->setCustomName('§r§4Punish');
                $clock->getNamedTag()->setInt(self::TAG_PUNISH, 1);
                return $clock;
            case 'name':
                $ghast_tear = VanillaItems::GHAST_TEAR()
                    ->setCustomName('§r§cSee Name');
                $ghast_tear->getNamedTag()->setInt(self::TAG_TRUE_NAME, 1);
                return $ghast_tear;
            case 'vanished':
                $green_dye = VanillaItems::GREEN_DYE()
                    ->setCustomName('§r§7You are §avanished');
                $green_dye->getNamedTag()->setInt(self::TAG_VANISH, 1);
                return $green_dye;
            case 'unvanished':
                $gray_dye = VanillaItems::GRAY_DYE()
                    ->setCustomName('§r§7You are §cunvanished');
                $gray_dye->getNamedTag()->setInt(self::TAG_UNVANISH, 1);
                return $gray_dye;
        }
        return null;
    }

    /**
     * @throws ReflectionException
     */
    public function fuckOffHotbar($player, $callBack, $time = 10) {
        /** @var HotbarMain $hotbar */
        $hotbar = $this->plugin->getServer()->getPluginManager()->getPlugin('vHotbar');
        if(!$hotbar) return;

        $hotbar->getHotbarUsers()->remove($player);
        $task = new DelayedHotBar($player, $this, $callBack);
        $this->plugin->getScheduler()->scheduleDelayedTask($task, $time);
    }

    public static function isEnabled($player) : ?bool {
        if($player instanceof Player) {
            $player = $player->getName();
        }
        return (isset(self::$enabled[$player]));
    }
}