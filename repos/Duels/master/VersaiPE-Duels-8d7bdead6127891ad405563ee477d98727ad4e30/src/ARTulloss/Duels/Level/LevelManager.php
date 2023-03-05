<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Level;

use pocketmine\level\generator\Flat;
use pocketmine\level\Level;

use ARTulloss\Duels\Duels;
use ARTulloss\Duels\Level\Task\RecursiveRmdirTask;
use ARTulloss\Duels\Level\Task\RecursiveCopyTask;

use function gettype;

/**
 * Class LevelManager
 * @package ARTulloss\Duels\Arena
 */
class LevelManager
{
	/** @var array $tempLevels */
	private $tempLevels = [];
	/** @var Duels $duels */
	private $duels;
	/** @var string $path */
	private $path;

	/**
	 * LevelManager constructor.
	 * @param Duels $duels
	 */
	public function __construct(Duels $duels)
	{
		$this->duels = $duels;
		$this->path = $this->duels->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR;
	}

	public function __destruct() {
		$this->deleteAllTempLevels();
	}

	public function deleteAllTempLevels(): void{
		// Bye
		foreach ($this->tempLevels as $levelDir)
		    if(gettype($levelDir) === 'string')
		        $this->recursive_rmdir_async($levelDir);
	}

	/**
	 * @param string $levelDir
	 */
	public function recursive_rmdir_async(string $levelDir): void{
		$server = $this->duels->getServer();
		$task = new RecursiveRmdirTask($levelDir, function () use ($server): void {
			$server->getLogger()->info('Removed level successfully!');
		});
		$server->getAsyncPool()->submitTask($task);
	}

	/**
	 * For future reading, this works by creating an empty flat world,
	 * then deleting the flat world's region files and unloading it,
	 * replacing it with the files from the template file, then reloading it
	 *
	 * @param Level $level
	 * @param string $newLevelName
	 * @param callable|null $callback
	 */
	public function copyLevel(Level $level, string $newLevelName, ?callable $callback): void{
		$server = $this->duels->getServer();

		// Generate a flat world
		$server->generateLevel($newLevelName, 0, Flat::class);

		// Unload it
		$server->unloadLevel($server->getLevelByName($newLevelName));

		// Copy the world
		$src = $this->path . $level->getFolderName() . DIRECTORY_SEPARATOR . 'region';
		$dst = $this->path . $newLevelName . DIRECTORY_SEPARATOR . 'region';
		$task = new RecursiveCopyTask($src, $dst, $callback);
		$this->duels->getServer()->getAsyncPool()->submitTask($task);
	}

	/**
	 * @param string $src
	 * @param string $dst
	 * @param callable $callback
	 */
	public function recurse_copy_async(string $src, string $dst, callable $callback): void{
		$task = new RecursiveCopyTask($src, $dst, $callback);
		$this->duels->getServer()->getAsyncPool()->submitTask($task);
	}

	/**
	 * @param string $tempLevelName
	 */
	public function deleteTempLevel(string $tempLevelName): void{
		$tempLevelDir = $this->tempLevels[$tempLevelName];
		// Unset it
		unset($this->tempLevels[$tempLevelName]);
		// Delete the level
		$this->recursive_rmdir_async($tempLevelDir);
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

}