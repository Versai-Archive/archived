<?php


namespace Sam\VCrates;


use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\block\Block;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use Sam\VCrates\constants\Messages;
use Sam\VCrates\database\Manager;
use Sam\VCrates\tile\VCrateTile;

class EventListener implements Listener{

	private Main $plugin;
	private Manager $database;

	public function __construct(Main $plugin, $database){
		$this->plugin = $plugin;
		$this->database = $database;
	}


	public function onPickup(InventoryPickupItemEvent $event){
		if($event->getItem()->getItem()->getNamedTag()->getString("Crate") == "preview") $event->setCancelled();
	}

	public function onInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		$username = $event->getPlayer()->getName();
		$block = $event->getBlock();
		$level = $block->getLevel();

		if($block->getId() === Block::CHEST){
			$tile = $level->getTile($block);


			if($tile instanceof VCrateTile){
				if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
					$event->setCancelled();
					$form = new MenuForm(
						$tile->getName(),
						"What do you want to do?",
						[
							new MenuOption("View Crate Content"),
							new MenuOption("Open a Crate"),
							new MenuOption("Instantly Open Crate")
						],

						function(Player $submitter, int $selected) use ($tile) : void{

							switch($selected){
								case 0:
									$tile->viewContent($submitter);
									break;
								case 1:
									$tile->checkCrate($submitter, $this->database);
									break;
								case 2:
									$tile->checkCrate($submitter, $this->database, true);
									break;
							}
						},
					);

					$player->sendForm($form);
				}
				$event->setCancelled();
				return;
			}

			if($tile instanceof Chest && isset($this->plugin->placeCrate[$username])){
				$nbt = $tile->getSpawnCompound();
				$nbt->setString("CrateType", $this->plugin->placeCrate[$username]);
				$newTile = Tile::createTile("VCrateTile", $level, $nbt, $this->plugin->placeCrate[$username]);
				$newTile->spawnToAll();
				$tile->close();
				$player->sendMessage(Messages::YES_PREFIX . Messages::PLACED);
				unset($this->plugin->placeCrate[$username]);
				$event->setCancelled();
				return;
			}
		}
	}

}