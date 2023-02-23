<?php
declare(strict_types=1);

namespace Versai\vlobby;

use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerChatEvent;

use Versai\vlobby\libs\jojoe77777\FormAPI\SimpleForm;
use Versai\vlobby\libs\libpmquery\PMQuery;
use Versai\vlobby\libs\libpmquery\PmQueryException;
use Versai\vlobby\task\InfoTask;
use Versai\vlobby\task\SpawnParticlesTask;

use function array_search;
use function strpos;
use function ucwords;

class Main extends PluginBase implements Listener{

    //To Prevent Advertising
    public const LINKS = [".leet.cc", ".net", ".com", ".us", ".co", ".co.uk", ".ddns", ".ddns.net", ".cf", ".me", ".cc", ".ru", ".eu", ".tk", ".gq", ".ga", ".ml", ".org", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9", "nethergames", "fallentech", "mineplex", ".gg", "syn", "synhcf", "ownage", "discord.gg"];

    /** @var string[] */
    private array $hideAll = [];
    /** @var array $navigatorInfo */
    public array $navigatorButtonInfo = [];
    /** @var array $navigatorAddressInfo */
    public array $navigatorAddressInfo = [];

    public function onEnable() : void{
        $this->getScheduler()->scheduleRepeatingTask(new SpawnParticlesTask($this), 10);
        $this->getScheduler()->scheduleRepeatingTask(new InfoTask($this), 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getNetwork()->setName("§3§lHUB §6➼ §r§9versai.pro§r");
    }

    public function onPlayerJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        $event->setJoinMessage("§7[§a+§7] §a" . $player->getName());

        $player->setHealth(20);
        $player->getHungerManager()->setFood(20);

        $inventory = $player->getInventory();

        $inventory->clearAll();
        $inventory->setItem(0, VanillaItems::NETHER_STAR()->setCustomName(TextFormat::GREEN . "Info"));
        $inventory->setItem(4, VanillaItems::COMPASS()->setCustomName(TextFormat::YELLOW . "Navigator"));
        $inventory->setItem(7, VanillaItems::FEATHER()->setCustomName(TextFormat::BLUE . "Fly"));
        $inventory->setItem(8, VanillaItems::STICK()->setCustomName(TextFormat::YELLOW . "Hide " . TextFormat::GREEN . "Players"));
    }

    public function onPlayerQuit(PlayerQuitEvent $event) : void{
        $event->setQuitMessage("§7[§c-§7] " . $event->getPlayer()->getName());
    }

    public function onPlayerChat(PlayerChatEvent $event) : void{
        $msg = $event->getMessage();
        $player = $event->getPlayer();

        foreach(self::LINKS as $link){
            if(strpos($msg, $link)){
                $player->sendMessage("§cDo not try to advertise! Advertising will lead you to a mute!");
                $event->cancel();
                return;
            }
        }
    }

    public function onEntityDamage(EntityDamageEvent $event) : void{
        $event->cancel();
    }

    public function onBlockPlace(BlockPlaceEvent $event) : void{
        if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
            $event->cancel();
        }
    }

    public function onBlockBreak(BlockBreakEvent $event) : void{
        if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
            $event->cancel();
        }
    }

    public function onPlayerExhaust(PlayerExhaustEvent $event) : void{
        $event->cancel();
    }

    public function onPlayerDropItem(PlayerDropItemEvent $event) : void{
        if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
            $event->cancel();
        }
    }

    public function onPlayerItemConsume(PlayerItemConsumeEvent $event) : void{
        if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
            $event->cancel();
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event) : void{
        if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
            $event->setDrops([]);
        }
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event) : void{
        $event->cancel();
    }

    public function onPlayerInteract(PlayerItemUseEvent $event) : void{
        $player = $event->getPlayer();
        $inventory = $player->getInventory();
        $item = $inventory->getItemInHand();
        if($item->getCustomName() === TextFormat::YELLOW . "Navigator"){
            $form = new SimpleForm(function(Player $sender, $data): void{
                if($data === null){
                    return;
                }

                foreach($this->navigatorButtonInfo as $name => $bData){
                    switch($data){
                        case $name:
                            $address = $this->navigatorAddressInfo[$name]["address"];
                            $port = $this->navigatorAddressInfo[$name]["port"];
                            $sender->transfer($address, $port);
                            break;
                        default:
                            break;
                    }
                }
            });
            $form->setTitle("§eServer Navigator");
            $form->setContent("§7Please select a server:");
            foreach($this->navigatorButtonInfo as $name => $data){
                $form->addButton($data["bd"], 0, $data["img"], $name);
            }
            $form->addButton("§cClick Here To Exit!", 0, "textures/blocks/barrier", "exit");
            $player->sendForm($form);
            return;
        }

        if($item->getCustomName() === TextFormat::GREEN . "Info"){
            $player->sendTitle("§6§oSoon...", "§aOur next update is being created!");
            return;
        }

        if($item->getCustomName() === TextFormat::BLUE . "Fly"){
            if(!$player->hasPermission("vlobby.fly")){
                $player->sendMessage("§cYou dont have permission to use this!");
                $player->sendMessage("§eYou must purchase a rank at §ashop.versai.pro §eto use lobby fly!");
                return;
            }

            $form = new SimpleForm(static function(Player $sender, $data){
                if($data === null){
                    return;
                }

                switch($data){
                    case 0;
                        $sender->setAllowFlight(true);
                        $sender->sendMessage("You haven enabled flight.§r");
                        break;
                    case 1;
                        $sender->setAllowFlight(false);
                        $sender->sendMessage("You have disabled flight.§r");
                        break;
                }
            });
            $form->setTitle("§9Versai Flight Settings");
            $form->setContent("§7Enable or Disable Flight.§r");
            $form->addButton("§l§aEnable Flight");
            $form->addButton("§l§cDisable Flight");
            $player->sendForm($form);
            return;
        }

        if($item->getCustomName() === TextFormat::YELLOW . "Hide " . TextFormat::GREEN . "Players"){
            $inventory->remove(VanillaItems::STICK()->setCustomName(TextFormat::YELLOW . "Hide " . TextFormat::GREEN . "Players"));
            $inventory->setItem(8, VanillaItems::BLAZE_ROD()->setCustomName(TextFormat::YELLOW . "Show " . TextFormat::GREEN . "Players"));

            $player->sendMessage(TextFormat::RED . "Disabled Player Visibility!");
            $this->hideAll[] = $player;
            foreach($this->getServer()->getOnlinePlayers() as $p2){
                $player->hidePlayer($p2);
            }
        }elseif($item->getCustomName() === TextFormat::YELLOW . "Show " . TextFormat::GREEN . "Players"){
            $inventory->remove(VanillaItems::BLAZE_ROD()->setCustomName(TextFormat::YELLOW . "Show " . TextFormat::GREEN . "Players"));
            $inventory->setItem(8, VanillaItems::STICK()->setCustomName(TextFormat::YELLOW . "Hide " . TextFormat::GREEN . "Players"));

            $player->sendMessage(TextFormat::GREEN . "Enabled Player Visibility!");
            unset($this->hideAll[array_search($player, $this->hideAll)]);
            foreach($this->getServer()->getOnlinePlayers() as $p2){
                $player->showPlayer($p2);
            }
        }
    }

    public function getNavigationData(): void{
        $config = $this->getConfig();
        $serverOnline = null;
        $serverMax = null;
        foreach($config->getNested("servers") as $server => $data){
            $this->navigatorAddressInfo[$server] = $data;
            try {
                $query = PMQuery::query($data["address"], $data["port"]);
                $serverOnline = $query["Players"];
                $serverMax = $query["MaxPlayers"];
            } catch (PmQueryException $e){
                $serverOnline = -9999;
                $serverMax = 0;
            }

            if($serverOnline !== -9999) {
                $this->navigatorButtonInfo[$server]["bd"] = ucwords($server) . " [" . $serverOnline . "/" . $serverMax . "]\n§7Click to connect...";
            } else {
                $this->navigatorButtonInfo[$server]["bd"] = ucwords($server) . " [0/" . $serverMax . "]\n§4Server Offline...";
            }
            $this->navigatorButtonInfo[$server]["img"] = $data["img"];
        }
    }
}