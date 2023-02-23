<?php

namespace Versai\Sumo\Task;


use Closure;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

class CountdownBroadcastTask extends Task
{
    private int $time;

    private string $formatting;

    private Plugin $plugin;
    private ?Closure $afterTask;
    private bool $withZero;

    public function __construct(Plugin $plugin, int $time, bool $withZero = true, string $formatting = "§c{countdown}", ?Closure $afterTask = null)
    {
        $this->time = $time;
        $this->formatting = $formatting;
        $this->plugin = $plugin;
        $this->withZero = $withZero;
        $plugin->getScheduler()->scheduleRepeatingTask($this, 20);
        $this->afterTask = $afterTask;
    }

    public function onRun(int $currentTick)
    {
        $this->plugin->getServer()->broadcastMessage(str_replace("{countdown}", $this->time, $this->formatting));
        $this->time--;
        if ($this->withZero) {
            if ($this->time < 0) {
                $this->endTask();
            }
        } else {
            if ($this->time <= 0) {
                $this->endTask();
            }
        }
    }

    public function endTask() {
        $this->plugin->getScheduler()->cancelTask($this->getTaskId());
        if ($this->afterTask !== null) ($this->afterTask)();
    }
}