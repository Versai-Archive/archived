<?php

namespace Versai\vStaff\classes;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\inventory\transaction\action\CreateItemAction;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\world\World;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\vStaff\Main;
use Versai\vStaff\math\Entities;
use Versai\vStaff\tasks\CPSHandlerTask;
use Versai\vStaff\tasks\DelayedHotBar;
use Versai\vStaff\libs\jojoe77777\FormAPI\SimpleForm;
use Versai\Hotbar\Main as HotbarMain;
use ReflectionException;

class vStaff {

	public array $locked = [];
	public array $items = [
		'compass' => '345:0:§r§7Compass',
		'unlock' => '280:0:§r§6Unlock §7from player',
		'lock' => '369:0:§r§6Lock §7to player',
		'cps' => '340:0:§r§7Check CPS',
		'freeze' => '79:0:§r§bFreeze §7Player',
		'player' => '339:0:§r§7Player Info',
		'punish' => '347:0:§r§4Punish',
		'unvanished' => '351:8:§r§7You are §cunvanished',
		'name' => '370:0:§r§cSee Name',
		'vanished' => '351:10:§r§7You are §avanished'
	];
	private Main $plugin;
	private array $enabled = [];
	private array $cache = [];
	private array $touchCool = [];
	private array $cpsCooldown = [];
	private array $tap2punish = [];
	private array $isVanished = [];

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	public function enable(Player $player): bool {
		$pk = new InventoryContentPacket();
		$pk->windowId = 121;
		$pk->items = [];
		$player->getNetworkSession()->sendDataPacket($pk);
		$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, true);
		$player->setNameTag('§c[V]§r ' . str_replace('§c[V]§r ', '', $player->getNameTag()));
		$this->cache[$player->getName()] = [
			'gamemode' => $player->getGamemode(),
			'inventory' => $player->getInventory()->getContents(),
			'armor' => $player->getArmorInventory()->getContents()
		];
		$this->enabled[$player->getName()] = true;

		$player->setGamemode(GameMode::CREATIVE());
		$player->getArmorInventory()->clearAll();
		$inventory = $player->getInventory();
		$inventory->clearAll();
		$inventory->setItem(0, $this->getItem($this->items['compass']));
		$inventory->setItem(1, $this->getItem($this->items['lock']));
		$inventory->setItem(2, $this->getItem($this->items['cps']));
		$inventory->setItem(3, $this->getItem($this->items['freeze']));
		$inventory->setItem(5, $this->getItem($this->items['player']));
		$inventory->setItem(6, $this->getItem($this->items['punish']));
		$inventory->setItem(7, $this->getItem($this->items['name']));
		$inventory->setItem(8, $this->getItem($this->items['vanished']));

		foreach($this->plugin->getServer()->getOnlinePlayers() as $pl) {
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

	public function getItem(string $itemStr) : ?Item {
		$str = explode(':', $itemStr);
		$id = $str[0];
		$meta = $str[1];
		$name = $str[2];
		$item = ItemFactory::getInstance()->get($id, $meta);
		$item->setCustomName($name);
		return $item;
	}

	public function disable(Player $player): bool
    {
		/* Forcefully disable a HUD for a user. */
        if(!isset($this->enabled[$player->getName()])) {
		    return false;
        }
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$this->unvanishPlayer($player); // Forcefully unvanish user.
		unset($this->enabled[$player->getName()]);
		if(!isset($this->cache[$player->getName()])) {
			$player->setGamemode(GameMode::SURVIVAL());
		} else {
			$data = $this->cache[$player->getName()];
			$player->getInventory()->setContents($data['inventory']);
			$player->getArmorInventory()->setContents($data['armor']);
			$player->setGamemode($data['gamemode']);
			unset($this->cache[$player->getName()]);
		}
        return true;
	}

	private function unvanishPlayer(Player $player) : void
    {
		$player->setNameTag(str_replace('§c[V]§r ', '', $player->getNameTag()));
		$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, false);
		foreach($this->plugin->getServer()->getOnlinePlayers() as $pl) {
			$pl->showPlayer($player);
		}
    }

	public function handleDrop(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		if($player->isClosed()) return;
		if(!isset($this->enabled[$player->getName()])) return;

		$event->cancel();
	}

	public function handleTransaction(InventoryTransactionEvent $event){
		$player = $event->getTransaction()->getSource();
		if(!$player instanceof Player) return;
		if($player->isClosed()) return;
		if(!isset($this->enabled[$player->getName()])) return;
		foreach($event->getTransaction()->getActions() as $action){
			if($action instanceof CreateItemAction){
				$event->cancel();
			}
		}
	}

	public function handleClick($event)
    {
        $player = $event->getPlayer();
        if ($player->isClosed()) return;
        if (!$player instanceof Player) return;
        if (!isset($this->enabled[$player->getName()])) return;
        $event->cancel();
        if (isset($this->touchCool[$player->getName()])) {
            if ($this->touchCool[$player->getName()] + 0.3 >= microtime(true)) return;
        }

        $this->touchCool[$player->getName()] = microtime(true);
        $inventory = $player->getInventory();
        $item = $inventory->getItemInHand();
        $compass = $this->getItem($this->items['compass']);
        $freeze = $this->getItem($this->items['freeze']);
        $unlock = $this->getItem($this->items['unlock']);
        $lock = $this->getItem($this->items['lock']);
        $vanished = $this->getItem($this->items['vanished']);
        $unvanished = $this->getItem($this->items['unvanished']);

        if ($item->getId() === $compass->getId() && $compass->getCustomName() === $compass->getCustomName()) {
            // Teleport where player is looking.
            $tpTo = $this->getTpTo($player);
            if (!$tpTo) return;
            $player->teleport($tpTo);
        }

        if ($item->getId() === $lock->getId()) {
            $ent = Entities::getNearestEntityLookingAt($player, 20);
            if (!$ent instanceof Player) return;
            $this->locked[$player->getName()] = $ent->getName();
            $inventory->setItem(1, $unlock);
            $player->sendPopup($unlock->getCustomName() . " §f{$ent->getName()}");

            return;
        }
        if ($item->getId() === $freeze->getId() && $freeze->getCustomName() === $freeze->getCustomName()) {
            $ent = Entities::getNearestEntityLookingAt($player, 20);
            if (!$ent instanceof Player) return;
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

		if($item->getId() === $unlock->getId() && $unlock->getCustomName() === $unlock->getCustomName()){
			if(!isset($this->locked[$player->getName()])) return;
			$inventory->setItem(1, $lock);
			$player->sendPopup($lock->getCustomName() . " §f{$this->locked[$player->getName()]}");
			unset($this->locked[$player->getName()]);
			return;
		}

		if($item->getId() === $vanished->getId() && $item->getCustomName() === $vanished->getCustomName()){
			$inventory->setItem(8, $this->getItem($this->items['unvanished']));
			$player->sendPopup($unvanished->getCustomName());
			$this->unvanishPlayer($player);
			return;
		}
		if($item->getId() === $unvanished->getId() && $item->getMeta() === $unvanished->getMeta() && $item->getCustomName() === $unvanished->getCustomName()){
			$inventory->setItem(8, $this->getItem($this->items['vanished']));
			$player->sendPopup($vanished->getCustomName());
			$this->vanishPlayer($player);
			return;
		}
	}

	public function getTpTo(Player $player) : ?Vector3{
		/**
		 * Credit: AimTP @Dylan
		 */
		$start = $player->getPosition()->add(0, $player->getEyeHeight(), 0);
		$dirVec = $player->getDirectionVector()->multiply($player->getViewDistance() * 16);
		$end = $start->add($dirVec->getX(), $dirVec->getY(), $dirVec->getZ());
		$level = $player->getWorld();
		foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3){
			if($vector3->y >= World::Y_MAX or $vector3->y <= 0){
				return null;
			}
			if(!$level->isChunkLoaded($vector3->x >> 4, $vector3->z >> 4)){
				return null;
			}
			if(($result = $level->getBlockAt($vector3->x, $vector3->y, $vector3->z)->calculateIntercept($start, $end)) !== null){
				if($level->getBlockAt($vector3->x, $vector3->y, $vector3->z)->isTransparent()) continue;
				$target = $result->hitVector;
				return $target;
			}
		}
		return null;
	}

	public function vanishPlayer(Player $player) {
		$player->setNameTag('§c[V]§r ' . str_replace('§c[V]§r ', '', $player->getNameTag()));
		$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SILENT, true);
		$player->setGamemode(GameMode::CREATIVE());
		foreach($this->plugin->getServer()->getOnlinePlayers() as $pl) {
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

	public function handleJoin($event) {
		$player = $event->getPlayer();
		if(!$player) return;

		foreach($this->enabled as $p => $time) {
			$pl = $this->plugin->getServer()->getPlayerByPrefix($p);
			if(!$pl) {
				unset($this->enabled[$p]);
				continue;
			} else {
				if($pl->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
					if(!$player->hasPermission('StaffHUD.staffmode.see_op')) {
					    $player->hidePlayer($pl);
                    }
					continue;
				}
				if(!$player->hasPermission('StaffHUD.staffmode.see')) {
				    $player->hidePlayer($pl);
                }
			}
		}
	}

	public function handleQuit($event) {
        /** @var Player $player */
		$player = $event->getPlayer();
		if($this->isEnabled($player)) {
		    unset($this->enabled[$player->getName()]);
        }
		if(isset($this->locked[$player->getName()])) {
		    unset($this->locked[$player->getName()]);
        }
	}

	public function isEnabled($player) : ?bool {
		if($player instanceof Player) {
		    $player = $player->getName();
        }
		return (isset($this->enabled[$player]));
	}

	public function handleActions($event) {
        /** @var Player $player */
		$player = $event->getPlayer();
		if($player->isClosed()) {
		    return;
        }
		if(!isset($this->enabled[$player->getName()])) {
		    return;
        }

		$event->cancel();
	}

	public function handleMove($event) {
	    /** @var Player $player */
		$player = $event->getPlayer();
		foreach($this->locked as $mod => $p) {
			if($p === $player->getName()) {
				$mod = $this->plugin->getServer()->getPlayerByPrefix($mod);
				if(!$mod) {
				    continue;
                }
				$distance = $this->getDistance($mod, $player);

				if($distance >= 10) {
					$mod->teleport(new Position($player->getPosition()->x, $player->getPosition()->y, $player->getPosition()->z, $player->getWorld()));
					$mod->sendTip("§r§7Teleported to §flocked§7 player: §f{$p}§7.");
				}
			}
		}
	}

	public function getDistance(Player $dam, Player $p) {
		$dx = $dam->getPosition()->getX();
		$dy = $dam->getPosition()->getY();
		$dz = $dam->getPosition()->getZ();
		$px = $p->getPosition()->getX();
		$py = $p->getPosition()->getY();
		$pz = $p->getPosition()->getZ();

		$distanceX = sqrt(pow(($px - $dx), 2) + pow(($py - $dy), 2));
		$distanceZ = sqrt(pow(($pz - $dz), 2) + pow(($py - $dy), 2));
		return (abs($distanceX) > abs($distanceZ)) ? abs($distanceX) : abs($distanceZ);
	}

	public function handleDamage($event) {
		$ent = $event->getEntity();
		$dam = $event->getDamager();

		if(!$ent instanceof Player) {
		    return;
        }
		if(!$dam instanceof Player) {
		    return;
        }
		if(isset($this->enabled[$dam->getName()])) {
			$event->cancel();
		}
		if(isset($this->enabled[$ent->getName()])) {
			$this->fuckOffHotbar($ent, function($player, $HUD) {
				$player->setNameTag('§c[V]§r ' . str_replace('§c[V]§r ', '', $player->getNameTag()));
				$inventory = $player->getInventory();
				$inventory->clearAll();
				$inventory->setItem(0, $this->getItem($this->items['compass']));
				$inventory->setItem(1, $this->getItem($this->items['lock']));
				$inventory->setItem(2, $this->getItem($this->items['cps']));
				$inventory->setItem(3, $this->getItem($this->items['freeze']));
				$inventory->setItem(5, $this->getItem($this->items['player']));
				$inventory->setItem(6, $this->getItem($this->items['punish']));
				$inventory->setItem(7, $this->getItem($this->items['name']));
				$inventory->setItem(8, $this->getItem($this->items['vanished']));
				if(isset($HUD->locked[$player->getName()])) {
					$inventory->setItem(1, $HUD->getItem($HUD->items['unlock']));
				}
			});
		}

		$player = $dam;
		if($player->isClosed()) {
		    return;
        }
		if(!isset($this->enabled[$player->getName()])) {
		    return;
        }
		if(isset($this->touchCool[$player->getName()])) {
			if($this->touchCool[$player->getName()] + 0.3 >= microtime(true)) {
			    return;
            }
		}
		$this->touchCool[$player->getName()] = microtime(true);
		$inventory = $player->getInventory();
		$item = $inventory->getItemInHand();

		$lock = $this->getItem($this->items['lock']);
		$unlock = $this->getItem($this->items['unlock']);
		$freeze = $this->getItem($this->items['freeze']);
		$cps = $this->getItem($this->items['cps']);
		$pinfo = $this->getItem($this->items['player']);
		$name = $this->getItem($this->items['name']);
		$punish = $this->getItem($this->items['punish']);

		switch($item->getCustomName()) {
			case $lock->getCustomName():
				$this->locked[$player->getName()] = $ent->getName();
				$inventory->setItem(1, $unlock);
				$player->sendPopup($unlock->getCustomName());
				break;
			case $unlock->getCustomName():
				unset($this->locked[$player->getName()]);
				$inventory->setItem(1, $lock);
				break;
			case $freeze->getCustomName():
				if($ent->isImmobile()) {
					$player->sendPopup("§7Thawed §b{$ent->getName()}");
					$ent->setImmobile(false);
				} else {
					$player->sendPopup("§7Froze §b{$ent->getName()}");
					$ent->setImmobile(true);
				}
				break;
			case $pinfo->getCustomName():
				$this->plugin->getServer()->dispatchCommand($dam, "pinfo " . $ent->getName());
				break;
			case $name->getCustomName():
				$dam->sendPopup($ent->getName());
				break;
			case $punish->getCustomName():
				$this->tap2punish[$player->getName()] = $ent->getName();

				$closure = function(Player $player, $data): void{
				    if($data !== null) {
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
				break;
			case $cps->getCustomName():
				if(isset($this->cpsCooldown[$player->getName()]) && $this->cpsCooldown[$player->getName()] + 6 >= microtime(true)) {
					$player->sendMessage(TextFormat::RED . "Woah! Slowdown there.");
					break;
				} else {
					$this->cpsCooldown[$player->getName()] = microtime(true);
					$this->plugin->cps[$ent->getName()] = 0;
					$this->plugin->getScheduler()->scheduleDelayedTask(new CPSHandlerTask($this->plugin, $dam, $ent->getName()), 20 * 5);
					$dam->sendMessage(TextFormat::GREEN . "Started counting " . $ent->getName() . "'s cps");
				}
				break;
		}

	}

    /**
     * @throws ReflectionException
     */
    public function fuckOffHotbar($player, $callBack, $time = 10) {
	    /** @var HotbarMain $hotbar */
		$hotbar = $this->plugin->getServer()->getPluginManager()->getPlugin('Hotbar');
		if(!$hotbar) {
            return;
        }
		$hotbar->getHotbarUsers()->remove($player);
		$task = new DelayedHotBar($player, $this, $callBack);
		$this->plugin->getScheduler()->scheduleDelayedTask($task, $time);
	}

	public function handleTeleport($event) {
		$player = $event->getEntity();
		$from = $event->getFrom();
		$to = $event->getTo();

		if(!$player instanceof Player) {
		    return;
        }
		if(!isset($this->enabled[$player->getName()])) {
		    return;
        }
		if($from->getWorld()->getDisplayName() !== $to->getWorld()->getDisplayName()) {
			$this->fuckOffHotbar($player, function($player, $HUD) {
				$inventory = $player->getInventory();
				$inventory->clearAll();
				$inventory->setItem(0, $this->getItem($this->items['compass']));
				$inventory->setItem(1, $this->getItem($this->items['lock']));
				$inventory->setItem(2, $this->getItem($this->items['cps']));
				$inventory->setItem(3, $this->getItem($this->items['freeze']));
				$inventory->setItem(5, $this->getItem($this->items['player']));
				$inventory->setItem(6, $this->getItem($this->items['punish']));
				$inventory->setItem(7, $this->getItem($this->items['name']));
				$inventory->setItem(8, $this->getItem($this->items['vanished']));
				$this->vanishPlayer($player);

				if(isset($HUD->locked[$player->getName()])) {
					$inventory->setItem(1, $HUD->getItem($HUD->items['unlock']));
				}
			});

			foreach($this->locked as $mod => $e) {
				if($e == $player->getName()){
					$this->plugin->getServer()->getPlayerByPrefix($mod)->teleport($player->getPosition());
				}
			}
		}
	}
}