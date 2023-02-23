<?php

namespace Versai\vlobby\task;

use pocketmine\scheduler\Task;
use Versai\vlobby\Main;

class InfoTask extends Task{

    /** @var Main $plugin */
    private Main $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onRun(): void{
        $this->plugin->getNavigationData();
    }
}