<?php

declare(strict_types = 1);

/**
 * This file is for displaying player info in an actionbar
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Tasks;

use Versai\RPGCore\Indicators\IndicatorManager;
use pocketmine\scheduler\Task;

class IndicatorRemoveTask extends Task {

    private int $eid;
    
    public function __construct($eid) {
        $this->eid = $eid;
    }

    public function onRun() : void {
        IndicatorManager::getInstance()->removeTag($this->eid);
    }
}