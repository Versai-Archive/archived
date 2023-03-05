<?php
declare(strict_types=1);

namespace Versai\vTime;

use pocketmine\plugin\PluginBase;
use Versai\vTime\commands\OnlineTime;
use Versai\vTime\data\DatabaseContext;

class Main extends PluginBase{

	private DatabaseContext $database;

	public function onEnable(): void{
		$this->saveConfig();
		$this->getServer()->getLogger()->info("Enabled.");
		$this->database = new DatabaseContext($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this, $this->database), $this);
		$this->database->init();
		$this->initCommands();
	}

	private function initCommands(){
		$this->getServer()->getCommandMap()->register("vtime", new OnlineTime($this->database, $this));
	}

	public function onDisable(): void{
		$this->database->close();
	}
}
