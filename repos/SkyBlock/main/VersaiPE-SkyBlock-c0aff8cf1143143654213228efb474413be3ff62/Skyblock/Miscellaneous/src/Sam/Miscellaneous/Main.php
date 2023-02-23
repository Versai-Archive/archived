<?php

declare(strict_types=1);

namespace Sam\Miscellaneous;


use pocketmine\plugin\PluginBase;
use Sam\Miscellaneous\database\Manager;

class Main extends PluginBase{

	private Manager $database;

	public function onEnable() : void{
		$this->saveDefaultConfig();
		$this->database = new Manager($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this->database), $this);
	}

	public function onDisable() : void{
		if(isset($this->database)){
			$this->database->close();
		}
	}
}
