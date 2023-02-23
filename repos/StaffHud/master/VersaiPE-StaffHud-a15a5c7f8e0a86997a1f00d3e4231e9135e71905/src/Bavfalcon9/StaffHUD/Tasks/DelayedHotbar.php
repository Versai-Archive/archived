<?php

namespace Bavfalcon9\StaffHUD\Tasks;

use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;

class DelayedHotbar extends Task {
    private $player;
    private $callback;
    private $hud;

    public function __construct($player, $hud, $callback) {
        $this->player = $player;
        $this->callback = $callback;
        $this->HUD = $hud;
    }


    public function onRun(): void
    {

        $yikes = $this->callback;
        $yikes($this->player, $this->HUD);
    }


}