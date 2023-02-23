<?php

namespace Versai\vStaff;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use Versai\vStaff\classes\vStaff;
use Versai\vStaff\commands\Staff;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase{

    use SingletonTrait;

	public vStaff $vstaff;
	public array $cps;

	public function onEnable(): void {
	    self::setInstance($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->loadCommands();
	}


	private function loadCommands() {
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->registerAll('staffhud', [
			new Staff($this)
		]);
		$this->vstaff = new vStaff($this);
	}

	public function addClick(Player $player){
		$this->cps[$player->getName()] += 1;
	}
}