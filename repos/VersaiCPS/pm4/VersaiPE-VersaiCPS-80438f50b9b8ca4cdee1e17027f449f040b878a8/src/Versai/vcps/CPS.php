<?php

namespace Versai\vcps;

use Versai\vcps\command\CPSCommand;
use Versai\vcps\data\DataManager;
use pocketmine\plugin\PluginBase;

class CPS extends PluginBase {

    public function onEnable(): void {
        DataManager::init();
        new CPSListener($this);
        $this->getServer()->getCommandMap()->register($this->getName(), new CPSCommand());
    }

}