<?php

namespace ItemShow;

use pocketmine\{plugin\PluginBase}; //put together cuz pocketmine cant find shit cause i cant spell .-.

class Loader extends PluginBase{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }
}