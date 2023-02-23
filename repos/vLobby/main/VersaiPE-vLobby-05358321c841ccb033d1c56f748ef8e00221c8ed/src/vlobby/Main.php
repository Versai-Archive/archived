<?php

declare(strict_types=1);

namespace vlobby;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\Player;
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
use _640095b1e673c0584411jojoe77777\FormAPI\SimpleForm;
use pocketmine\event\player\PlayerChatEvent;

class Main extends PluginBase implements Listener{

	//To Prevent Advertising
	public const LINKS = [".leet.cc", ".net", ".com", ".us", ".co", ".co.uk", ".ddns", ".ddns.net", ".cf", ".me", ".cc", ".ru", ".eu", ".tk", ".gq", ".ga", ".ml", ".org", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9", "nethergames", "fallentech", "mineplex", ".gg", "syn", "synhcf", "ownage", "discord.gg"];

	/** @var string[] */
	private $hideAll = [];

	public function onEnable() : void{
		$this->getScheduler()->scheduleRepeatingTask(new SpawnParticlesTask($this), 10);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$event->setJoinMessage("§7[§a+§7] §a" . $player->getName());

		$player->setHealth(20);
		$player->setExhaustion(20);

		$inventory = $player->getInventory();

		$inventory->clearAll();
		$inventory->setItem(0, ItemFactory::get(ItemIds::NETHERSTAR)->setCustomName(TextFormat::GREEN . "Info"));
		$inventory->setItem(4, ItemFactory::get(ItemIds::COMPASS)->setCustomName(TextFormat::YELLOW . "Navigator"));
		$inventory->setItem(7, ItemFactory::get(ItemIds::FEATHER)->setCustomName(TextFormat::BLUE . "Fly"));
		$inventory->setItem(8, ItemFactory::get(ItemIds::STICK)->setCustomName(TextFormat::YELLOW . "Hide " . TextFormat::GREEN . "Players"));
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
				$event->setCancelled();
				return;
			}
		}
	}

	public function onEntityDamage(EntityDamageEvent $event) : void{
		$event->setCancelled();
	}

	public function onBlockPlace(BlockPlaceEvent $event) : void{
		if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
			$event->setCancelled();
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
			$event->setCancelled();
		}
	}

	public function onPlayerExhaust(PlayerExhaustEvent $event) : void{
		$event->setCancelled();
	}

	public function onPlayerDropItem(PlayerDropItemEvent $event) : void{
		if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
			$event->setCancelled();
		}
	}

	public function onPlayerItemConsume(PlayerItemConsumeEvent $event) : void{
		if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
			$event->setCancelled();
		}
	}

	public function onPlayerDeath(PlayerDeathEvent $event) : void{
		if(!$event->getPlayer()->hasPermission("vlobby.bypass")){
			$event->setDrops([]);
		}
	}

	public function onInventoryTransaction(InventoryTransactionEvent $event) : void{
		$event->setCancelled();
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		$inventory = $player->getInventory();
		$item = $inventory->getItemInHand();
		if($item->getCustomName() === TextFormat::YELLOW . "Navigator"){
			$form = new SimpleForm(function(Player $sender, $data){
				if($data === null){
					return;
				}

				switch($data){
					case 0:
						$this->getServer()->getCommandMap()->dispatch($sender, "transferserver KitPvP");
						break;
					case 1:
						$this->getServer()->getCommandMap()->dispatch($sender, "transferserver Build");
						break;
					case 2:
						break;
				}
			});
			$form->setTitle("§6Versai Network");
			$form->setContent("§7Please choose your server.");
			$form->addButton(TextFormat::BOLD . "§l§aKitPVP", 0, "textures/items/diamond_sword");
			$form->addButton(TextFormat::BOLD . "§l§bBuild", 0, "textures/blocks/grass_top");
			$form->addButton("§4Click Here To Exit!", 0, "textures/blocks/barrier");
			$player->sendForm($form);
			return;
		}

		if($item->getCustomName() === TextFormat::GREEN . "Info"){
			$player->sendTitle("§c§oSoon...", "§aNext update in working!");
			return;
		}

		if($item->getCustomName() === TextFormat::BLUE . "Fly"){
			if(!$player->hasPermission("vlobby.fly")){
				$player->sendMessage("§cYou dont have permission to use this!");
				$player->sendMessage("§eYou must have §aUltra §eor §1Elite §erank to use lobby fly!");
				return;
			}

			$form = new SimpleForm(static function(Player $sender, $data){
				if($data === null){
					return;
				}

				switch($data){
					case 0;
						$sender->setAllowFlight(true);
						$sender->sendMessage("§6[§1Versai§6]§a You haven enabled flight.§r");
						break;
					case 1;
						$sender->setAllowFlight(false);
						$sender->sendMessage("§6[§1Versai§6]§c You have disabled flight.§r");
						break;
				}
			});
			$form->setTitle("§6Versai Flight Settings");
			$form->setContent("§7Enable or Disable Flight.§r");
			$form->addButton("§l§aEnable Flight");
			$form->addButton("§l§cDisable Flight");
			$player->sendForm($form);
			return;
		}

		if($item->getCustomName() === TextFormat::YELLOW . "Hide " . TextFormat::GREEN . "Players"){
			$inventory->remove(ItemFactory::get(ItemIds::STICK)->setCustomName(TextFormat::YELLOW . "Hide " . TextFormat::GREEN . "Players"));
			$inventory->setItem(8, ItemFactory::get(ItemIds::BLAZE_ROD)->setCustomName(TextFormat::YELLOW . "Show " . TextFormat::GREEN . "Players"));

			$player->sendMessage(TextFormat::RED . "Disabled Player Visibility!");
			$this->hideAll[] = $player;
			foreach($this->getServer()->getOnlinePlayers() as $p2){
				$player->hidePlayer($p2);
			}
		}elseif($item->getCustomName() === TextFormat::YELLOW . "Show " . TextFormat::GREEN . "Players"){
			$inventory->remove(ItemFactory::get(ItemIds::BLAZE_ROD)->setCustomName(TextFormat::YELLOW . "Show " . TextFormat::GREEN . "Players"));
			$inventory->setItem(8, ItemFactory::get(ItemIds::STICK)->setCustomName(TextFormat::YELLOW . "Hide " . TextFormat::GREEN . "Players"));

			$player->sendMessage(TextFormat::GREEN . "Enabled Player Visibility!");
			unset($this->hideAll[array_search($player, $this->hideAll)]);
			foreach($this->getServer()->getOnlinePlayers() as $p2){
				$player->showPlayer($p2);
			}
		}
	}
}