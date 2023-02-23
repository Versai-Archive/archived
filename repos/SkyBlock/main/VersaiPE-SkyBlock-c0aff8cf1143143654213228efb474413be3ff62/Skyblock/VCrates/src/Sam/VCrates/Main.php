<?php

declare(strict_types=1);

namespace Sam\VCrates;


use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use Sam\VCrates\commands\GiveKeys;
use Sam\VCrates\commands\Keys;
use Sam\VCrates\commands\PlaceCrate;
use Sam\VCrates\commands\RemoveKeys;
use Sam\VCrates\commands\ResetKeys;
use Sam\VCrates\database\Manager;
use Sam\VCrates\tile\VCrateTile;

class Main extends PluginBase{

	private Manager $database;
	public array $placeCrate = [];

	private static Main $instance;


	public function onEnable() : void{
		$this->saveDefaultConfig();
		$this->database = new Manager($this);
		$this->initCommand();
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		Tile::registerTile(VCrateTile::class);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this, $this->database), $this);
		self::$instance = $this;
		
	}

	public function initCommand() : void{
		$this->getServer()->getCommandMap()->register("keys", new Keys($this->database));
		$this->getServer()->getCommandMap()->register("givekeys", new GiveKeys($this->database));
		$this->getServer()->getCommandMap()->register("removekeys", new RemoveKeys($this->database));
		$this->getServer()->getCommandMap()->register("resetkeys", new ResetKeys($this->database));
		$this->getServer()->getCommandMap()->register("placecrate", new PlaceCrate($this));
	}

	public function onDisable() : void{
		if(isset($this->database)){
			$this->database->close();
		}
	}

	public static function getInstance() : Main{
		return self::$instance;
	}

}
