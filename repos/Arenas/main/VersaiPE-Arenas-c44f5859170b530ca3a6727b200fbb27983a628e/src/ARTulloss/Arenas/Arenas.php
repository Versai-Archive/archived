<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 10/12/2018
 * Time: 9:13 AM
 */

declare(strict_types=1);

namespace ARTulloss\Arenas;

use ARTulloss\Arenas\Blocks\FrozenLava;
use ARTulloss\Arenas\Blocks\FrozenWater;
use ARTulloss\Arenas\Command\ArenaCommand;
use ARTulloss\Arenas\Events\Observer;
use pocketmine\block\BlockFactory;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;

use function file_put_contents;
use function file_get_contents;
use function file_exists;
use function mkdir;
use function scandir;
use function pathinfo;
use function json_decode;
use function str_replace;
use function explode;

class Arenas extends PluginBase
{
	/** @var Arenas $instance */
	public static $instance;
	/** @var Arena[] */
	public $arenas = [];
	/** @var array $defaults */
	public $defaults = [];
	/** @var string $path */
	public $path;

	/**
	 * @return Arenas
	 */
	public static function getInstance(): Arenas
	{
		return self::$instance;
	}

	public function onEnable(): void
	{

		// Singleton

		self::$instance = $this;

		$this->saveDefaultConfig();

		$this->defaults = $this->getConfig();

		$this->path = $this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR;

		$this->getServer()->getPluginManager()->registerEvents(new Observer($this), $this);

		if (!file_exists($this->path))
			mkdir($this->path);

		$this->loadArenas();

		// Until it is per-arena this is a quick-fix
		BlockFactory::registerBlock(new FrozenWater(), true);
		BlockFactory::registerBlock(new FrozenLava(), true);

		$this->getServer()->getCommandMap()->register("arenas", new ArenaCommand("arenas", $this));

	}

	public function loadArenas(): void
	{

		$i = 0;

		foreach (scandir($this->path) as $arenaName) {

			if (isset(pathinfo($this->path . $arenaName)['extension']) && pathinfo($this->path . $arenaName)['extension'] === 'json') {
				$data = json_decode(file_get_contents($this->path . $arenaName), true);
				$explosion = explode(":", $data["spawn"]);
				$name = str_replace(".json", '', $arenaName);
				$this->arenas[$name] = new Arena($name, $data["IDs"], new Position($explosion[0], $explosion[1], $explosion[2]), $data['settings']);
				$i++;
			}

		}

		$this->getLogger()->notice($i === 0 ? "No arenas loaded" : "There are " . $i . " arena(s) loaded");

	}

	/**
	 * @param Arena $arena
	 */
	public function registerArena(Arena $arena): void
	{
		$this->arenas[$arena->getName()] = $arena;
	}

	/**
	 * @param Arena $arena
	 * @return bool
	 */
	public function unregisterArena(Arena $arena): bool
	{
		if (isset($this->arenas[$arenaName = $arena->getName()])) {
			unset($this->arenas[$arenaName]);
			return true;
		}
		return false;
	}

	/**
	 * @param Level $level
	 * @return Arena|null
	 */
	public function getArenaByLevel(Level $level): ?Arena
	{
		$levelName = $level->getName();
		if (isset($this->arenas[$levelName]))
			return $this->arenas[$levelName];
		else
			return null;
	}

	/**
	 * @param Arena $arena
	 */
	public function saveArena(Arena $arena): void
	{
		file_put_contents($this->path . $arena->getName() . ".json", json_encode($arena->getAll(), JSON_PRETTY_PRINT));
	}

}
