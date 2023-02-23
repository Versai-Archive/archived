<?php


namespace Martin\GameAPI\Task\Async;


use Closure;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

class CopyDirectoryContentsAsyncTask extends AsyncTask
{
    private const ERROR = 0;
    private const SUCCESSFUL = 1;
    private string $sourceFolder;
    private string $destinationFolder;
    private ?Closure $onComplete;

    public function __construct(string $sourceFolder, string $destinationFolder, ?Closure $onComplete = null, $mcWorld = true)
    {
        $this->sourceFolder = $sourceFolder;
        $this->destinationFolder = $destinationFolder;
        if ($onComplete !== null) {
            Utils::validateCallableSignature(function (Server $server): void {
            }, $onComplete);
            $this->onComplete = $onComplete;
        }
    }

    public function onRun(): void # works fine
    {
        $this->copy($this->sourceFolder, $this->destinationFolder);
    }

    /**
     * @param string $sourceFolder
     * @param string $destinationFolder
     * @description Copied from Stackoverflow (https://stackoverflow.com/questions/2050859/copy-entire-contents-of-a-directory-to-another-using-php/2050965) with some small additions
     */
    private function copy(string $sourceFolder, string $destinationFolder): void
    {
        try {

            if (!is_dir($sourceFolder)) {
                $this->setResult(self::ERROR);
                return;
            }

            if (is_dir($destinationFolder)) {
                @rmdir($destinationFolder);
            }

            $dir = opendir($sourceFolder);

            if (!mkdir($destinationFolder) && !is_dir($destinationFolder)) {
                return;
            }

            while (false !== ($file = readdir($dir))) {
                if (($file !== ".") && ($file !== "..")) {
                    if (is_dir($sourceFolder . '/' . $file)) {
                        self::copy($sourceFolder . '/' . $file, $destinationFolder . '/' . $file);
                    } else {
                        copy($sourceFolder . '/' . $file, $destinationFolder . '/' . $file);
                    }
                }
            }

            closedir($dir);
            $this->setResult(self::SUCCESSFUL);

        } catch (\Exception $e) {
            $this->setResult(self::ERROR);
        }
    }

    public function onCompletion(Server $server): void # ???
    {
        if ($this->getResult() === self::SUCCESSFUL) {
            if ($this->onComplete !== null) {
                ($this->onComplete)($server);
            }
        } else {
            $server->getLogger()->error("[GameAPI] Could not copy folder {$this->sourceFolder} to {$this->destinationFolder}");
        }
    }
}