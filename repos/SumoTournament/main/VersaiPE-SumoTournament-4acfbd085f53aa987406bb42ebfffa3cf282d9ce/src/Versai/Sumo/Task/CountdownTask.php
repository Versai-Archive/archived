<?php


namespace Versai\Sumo\Task;


use Closure;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

class CountdownTask extends Task
{
    private int $time;

    /** @var Player[]  */
    private array $players;

    private string $formatting;

    private Plugin $plugin;
    private ?Closure $afterTask;

    public function __construct(Plugin $plugin, int $time, array $players, string $formatting = "§c{countdown}", ?Closure $afterTask = null)
    {
        $this->time = $time;
        $this->players = $players;
        $this->formatting = $formatting;
        $this->plugin = $plugin;
        $plugin->getScheduler()->scheduleRepeatingTask($this, 20);
        $this->afterTask = $afterTask;
    }

    public function onRun(int $currentTick)
    {
        foreach ($this->players as $player) $player->sendTitle(str_replace("{countdown}", $this->time, $this->formatting));
        $this->time--;
        if ($this->time <= 0) {
            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
            if ($this->afterTask !== null) ($this->afterTask)();
        }
    }
}