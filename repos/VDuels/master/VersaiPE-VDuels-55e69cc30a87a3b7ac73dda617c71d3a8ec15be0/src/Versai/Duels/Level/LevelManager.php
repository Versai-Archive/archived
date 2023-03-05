<?php
declare(strict_types=1);

namespace Versai\Duels\Level;

use pocketmine\world\generator\Flat;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use Versai\Duels\Duels;
use Versai\Duels\Level\Task\RecursiveCopyTask;
use Versai\Duels\Level\Task\RecursiveRmdirTask;
use function array_diff;
use function closedir;
use function copy;
use function gettype;
use function is_dir;
use function mkdir;
use function opendir;
use function readdir;
use function rmdir;
use function scandir;
use function unlink;

class LevelManager {

	/** @var array $tempLevels */
	private array $tempLevels = [];
	/** @var Duels $duels */
	private Duels $duels;
	/** @var string $path */
	private string $path;

	/**
	 * LevelManager constructor.
	 * @param Duels $duels
	 */
	public function __construct(Duels $duels) {
		$this->duels = $duels;
		$this->path = $this->duels->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR;
	}

	public function __destruct() {
		$this->deleteAllTempLevels();
	}

	public function deleteAllTempLevels(): void{
		foreach ($this->tempLevels as $levelDir) {
            if (gettype($levelDir) === 'string') {
                $this->recursive_rmdir_async($levelDir);
            }
        }
	}

	/**
	 * @param string $levelDir
	 */
	public function recursive_rmdir_async(string $levelDir): void{
		$server = $this->duels->getServer();
		$task = new RecursiveRmdirTask($levelDir, null);
		$server->getAsyncPool()->submitTask($task);
	}

    /**
     * @param World $level
     * @param string $newLevelName
     */
	public function copyLevel(World $level, string $newLevelName, ?callable $callback): void{
		$server = $this->duels->getServer();
        $worldMngr = $server->getWorldManager();

        $opts = new WorldCreationOptions();
        $opts->setGeneratorClass(Flat::class);
        $opts->setSeed(0);
        $worldMngr->generateWorld($newLevelName, $opts);

        $worldMngr->unloadWorld($worldMngr->getWorldByName($newLevelName));

		$src = $this->path . $level->getFolderName();
		$dst = $this->path . $newLevelName;
		$task = new RecursiveCopyTask($src, $dst, $callback);
		$this->duels->getServer()->getAsyncPool()->submitTask($task);
	}

	/**
	 * @param string $tempLevelName
	 */
	public function deleteTempLevel(string $tempLevelName): void{
	    if(isset($this->tempLevels[$tempLevelName])) {
            $tempLevelDir = $this->tempLevels[$tempLevelName];
            unset($this->tempLevels[$tempLevelName]);
            $this->recursive_rmdir_async($tempLevelDir);
        }
	}

	/**
	 * @param string $levelName
	 * @param string $path
	 */
	public function registerTempLevel(string $levelName, string $path): void{
		$this->tempLevels[$levelName] = $path;
	}

	/**
	 * @return string
	 */
	public function getPath(): string{
		return $this->path;
	}

    /**
     * @param $src
     * @param $dst
     * @param string $childFolder
     * @link https://stackoverflow.com/questions/2050859/copy-entire-contents-of-a-directory-to-another-using-php/2050965
     * StackOverFlow to the rescue
     */
    public static function recurseCopy($src, $dst, $childFolder = '') {
        $dir = opendir($src);
        @mkdir($dst);
        if ($childFolder != '') {
            @mkdir($dst . '/' . $childFolder);

            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        self::recurseCopy($src . '/' . $file, $dst . '/' . $childFolder . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $childFolder . '/' . $file);
                    }
                }
            }
        } else {
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        self::recurseCopy($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
        }

        closedir($dir);
    }

    public static function rmdirRecursive(string $dir) {
        if(is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? self::rmdirRecursive("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
        return false;
    }

}