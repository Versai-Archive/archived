<?php


namespace Martin\GameAPI\Task;


use Martin\GameAPI\Game\Maps\MDValueLevelName;
use Martin\GameAPI\Task\Async\CopyDirectoryContentsAsyncTask;
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\Level;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class CloneWorldTask extends Task
{
    private Level $level;
    private string $newLevel;

    public function __construct(Level $level, string $newLevel)
    {
        $this->level = $level;
        $this->newLevel = $newLevel;
    }

    public function onRun(int $currentTick): void
    {
        $destinationDirectory = $this->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $this->newLevel;
        $sourceDirectory = $this->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $this->level->getFolderName();

        $newLevel = $this->newLevel;
        $this->getServer()->getAsyncPool()->submitTask(new CopyDirectoryContentsAsyncTask($sourceDirectory, $destinationDirectory, function (Server $server) use ($newLevel): void {
            if ($server->loadLevel($newLevel)) {
                foreach ($server->getLevels() as $level) {
                    if ($level->getFolderName() === $newLevel) {
                        $provider = $level->getProvider();
                        if ($provider instanceof BaseLevelProvider) {
                            $provider->getLevelData()->setString("LevelName", $newLevel);
                            $provider->getLevelData()->safeClone();
                        }

                        $server->unloadLevel($level);
                        $server->loadLevel($newLevel);
                    }
                }
            }
        }));
    }

    private function getServer(): Server
    {
        return Server::getInstance();
    }

    /**
     * @return string
     */
    private function getNewLevel(): string
    {
        return $this->newLevel;
    }
}