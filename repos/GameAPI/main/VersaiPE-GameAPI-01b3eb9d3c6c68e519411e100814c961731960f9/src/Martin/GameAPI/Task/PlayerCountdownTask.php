<?php


namespace Martin\GameAPI\Task;


use Closure;
use Martin\GameAPI\GamePlugin;
use Martin\GameAPI\Utils\StringUtils;
use pocketmine\level\sound\ClickSound;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class PlayerCountdownTask extends Task
{
    private int $time;

    /** @var Player[] */
    private array $players;

    private string $formatting;

    private ?Closure $afterTask;

    private GamePlugin $plugin;

    public function __construct(GamePlugin $plugin, array $players, int $time = 10, string $formatting = "§c{countdown}", ?Closure $afterTask = null)
    {
        $this->time = $time;
        $this->players = $players;
        $this->formatting = $formatting;
        $this->plugin = $plugin;
        $this->afterTask = $afterTask;
        $plugin->getScheduler()->scheduleRepeatingTask($this, 20);
    }

    public function onRun(int $currentTick): void
    {
        foreach ($this->players as $player) {
            if (!($player instanceof Player)) {
                return;
            }

            if ($player->getLevel()) {
                $player->getLevel()->addSound(new ClickSound($player->getPosition()));
            }

            $player->sendTitle(StringUtils::replaceVars($this->formatting, ["countdown" => $this->time]));
        }

        $this->time--;
        if ($this->time <= 0) {
            $this->endTask();
        }
    }

    public function endTask(): void
    {
        $this->plugin->getScheduler()->cancelTask($this->getTaskId());
        if ($this->afterTask !== null) {
            ($this->afterTask)();
        }
    }
}