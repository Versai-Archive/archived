<?php

namespace Versai\vStaff;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Versai\vStaff\commands\Staff;

class Main extends PluginBase{

    use SingletonTrait;

	public array $cps;

	public function onEnable(): void {
	    self::setInstance($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->loadCommands();
	}


	private function loadCommands() {
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->registerAll('staffhud', [
			new Staff()
		]);
	}

	public function addClick(Player $player){
		$this->cps[$player->getName()] += 1;
	}
}