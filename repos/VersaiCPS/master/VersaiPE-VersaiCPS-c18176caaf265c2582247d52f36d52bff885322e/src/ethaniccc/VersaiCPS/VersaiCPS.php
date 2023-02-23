<?php

namespace ethaniccc\VersaiCPS;

use ethaniccc\VersaiCPS\command\CPSCommand;
use ethaniccc\VersaiCPS\data\DataManager;
use pocketmine\plugin\PluginBase;

class VersaiCPS extends PluginBase{

    public function onEnable(){
        DataManager::init();
        new CPSListener($this);
        $this->getServer()->getCommandMap()->register($this->getName(), new CPSCommand());
    }

}