<?php


namespace Martin\GameAPI\Task\Async;


use Closure;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RemoveDirectoryAsyncTask extends AsyncTask
{
    private string $targetDirectory;

    private Closure $onComplete;

    public function __construct(string $targetDirectory, ?Closure $onComplete = null)
    {
        $this->targetDirectory = $targetDirectory;
        if (!is_null($onComplete)) {
            Utils::validateCallableSignature(function (Server $server): void {
            }, $onComplete);
            $this->onComplete = $onComplete;
        }
    }

    public function onRun(): void
    {
        try {
            $this->rmdir_recursive($this->targetDirectory);
            $this->setResult("successfully");
        } catch (\Exception $e) {
            $this->setResult("crashed");
        }
    }

    public function rmdir_recursive($dirPath): bool
    {
        if (!empty($dirPath) && is_dir($dirPath)) {
            $dirObj = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS); //upper dirs not included,otherwise DISASTER HAPPENS :)
            $files = new RecursiveIteratorIterator($dirObj, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $path) {
                $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }
            rmdir($dirPath);
            return true;
        }
        return false;
    }

    public function onCompletion(Server $server): void
    {
        if ($this->onComplete !== null) {
            ($this->onComplete)($server);
        }
    }
}