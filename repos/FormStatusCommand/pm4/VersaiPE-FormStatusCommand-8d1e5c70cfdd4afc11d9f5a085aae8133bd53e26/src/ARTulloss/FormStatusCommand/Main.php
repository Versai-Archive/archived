<?php
declare(strict_types=1);

namespace ARTulloss\FormStatusCommand;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

	public function onEnable() : void{
	    $map = $this->getServer()->getCommandMap();
	    $map->unregister($map->getCommand('status'));
	    $map->register('pocketmine', new StatusCommand('status'));
	}
}
