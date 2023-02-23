<?php


namespace Martin\GameAPI\Task;


use Closure;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\utils\Utils;

class SleepTask extends Task
{
    private ?Closure $closure = null;

    public function __construct(Plugin $plugin, ?Closure $closure, int $delayedTick = 1)
    {
        Utils::validateCallableSignature(function (int $currentTick): void {
        }, $closure);
        $plugin->getScheduler()->scheduleDelayedTask($this, $delayedTick);
    }

    public function onRun(int $currentTick): void
    {
        if (!is_null($this->closure)) {
            ($this->closure)($currentTick);
        }
    }
}