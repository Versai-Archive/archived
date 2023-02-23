<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\world;

use pocketmine\entity\Entity;
use pocketmine\event\world\WorldInitEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\FormatConverter;
use pocketmine\world\format\io\WorldProvider;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\normal\Normal;
use function array_keys;
use function array_shift;
use function asort;
use function assert;
use function count;
use function implode;
use function microtime;
use function random_int;
use function round;
use function sprintf;
use function trim;
use const INT32_MAX;
use const INT32_MIN;

class WorldManager{
	/** @var World[] */
	private $worlds = [];
	/** @var World|null */
	private $defaultWorld;

	/** @var Server */
	private $server;

	/** @var bool */
	private $autoSave = true;
	/** @var int */
	private $autoSaveTicks = 6000;

	/** @var int */
	private $autoSaveTicker = 0;

	public function __construct(Server $server){
		$this->server = $server;

		$this->autoSave = $this->server->getConfigBool("auto-save", $this->autoSave);
		$this->autoSaveTicks = (int) $this->server->getProperty("ticks-per.autosave", 6000);
	}

	/**
	 * @return World[]
	 */
	public function getWorlds() : array{
		return $this->worlds;
	}

	/**
	 * @return World|null
	 */
	public function getDefaultWorld() : ?World{
		return $this->defaultWorld;
	}

	/**
	 * Sets the default world to a different world
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 *
	 * @param World|null $world
	 */
	public function setDefaultWorld(?World $world) : void{
		if($world === null or ($this->isWorldLoaded($world->getFolderName()) and $world !== $this->defaultWorld)){
			$this->defaultWorld = $world;
		}
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isWorldLoaded(string $name) : bool{
		return $this->getWorldByName($name) instanceof World;
	}

	/**
	 * @param int $worldId
	 *
	 * @return World|null
	 */
	public function getWorld(int $worldId) : ?World{
		return $this->worlds[$worldId] ?? null;
	}

	/**
	 * NOTE: This matches worlds based on the FOLDER name, NOT the display name.
	 *
	 * @param string $name
	 *
	 * @return World|null
	 */
	public function getWorldByName(string $name) : ?World{
		foreach($this->worlds as $world){
			if($world->getFolderName() === $name){
				return $world;
			}
		}

		return null;
	}

	/**
	 * @param World $world
	 * @param bool  $forceUnload
	 *
	 * @return bool
	 *
	 * @throws \InvalidArgumentException
	 */
	public function unloadWorld(World $world, bool $forceUnload = false) : bool{
		if($world === $this->getDefaultWorld() and !$forceUnload){
			throw new \InvalidArgumentException("The default world cannot be unloaded while running, please switch worlds.");
		}
		if($world->isDoingTick()){
			throw new \InvalidArgumentException("Cannot unload a world during world tick");
		}

		$ev = new WorldUnloadEvent($world);
		if($world === $this->defaultWorld and !$forceUnload){
			$ev->setCancelled(true);
		}

		$ev->call();

		if(!$forceUnload and $ev->isCancelled()){
			return false;
		}

		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.level.unloading", [$world->getDisplayName()]));
		foreach($world->getPlayers() as $player){
			if($world === $this->defaultWorld or $this->defaultWorld === null){
				$player->disconnect("Forced default world unload");
			}elseif($this->defaultWorld instanceof World){
				$player->teleport($this->defaultWorld->getSafeSpawn());
			}
		}

		if($world === $this->defaultWorld){
			$this->defaultWorld = null;
		}
		unset($this->worlds[$world->getId()]);

		$world->close();
		return true;
	}

	/**
	 * Loads a world from the data directory
	 *
	 * @param string $name
	 * @param bool   $autoUpgrade Converts worlds to the default format if the world's format is not writable / deprecated
	 *
	 * @return bool
	 *
	 * @throws WorldException
	 */
	public function loadWorld(string $name, bool $autoUpgrade = false) : bool{
		if(trim($name) === ""){
			throw new \InvalidArgumentException("Invalid empty world name");
		}
		if($this->isWorldLoaded($name)){
			return true;
		}elseif(!$this->isWorldGenerated($name)){
			return false;
		}

		$path = $this->server->getDataPath() . "worlds/" . $name . "/";

		$providers = WorldProviderManager::getMatchingProviders($path);
		if(count($providers) !== 1){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.level.loadError", [
				$name,
				empty($providers) ?
					$this->server->getLanguage()->translateString("pocketmine.level.unknownFormat") :
					$this->server->getLanguage()->translateString("pocketmine.level.ambiguousFormat", [implode(", ", array_keys($providers))])
			]));
			return false;
		}
		$providerClass = array_shift($providers);

		/**
		 * @var WorldProvider $provider
		 * @see WorldProvider::__construct()
		 */
		try{
			$provider = new $providerClass($path);
		}catch(CorruptedWorldException $e){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.level.loadError", [$name, "Corruption detected: " . $e->getMessage()]));
			return false;
		}catch(UnsupportedWorldFormatException $e){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.level.loadError", [$name, "Unsupported format: " . $e->getMessage()]));
			return false;
		}
		try{
			GeneratorManager::getGenerator($provider->getWorldData()->getGenerator(), true);
		}catch(\InvalidArgumentException $e){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.level.loadError", [$name, "Unknown generator \"" . $provider->getWorldData()->getGenerator() . "\""]));
			return false;
		}
		if(!($provider instanceof WritableWorldProvider)){
			if(!$autoUpgrade){
				throw new UnsupportedWorldFormatException("World \"$name\" is in an unsupported format and needs to be upgraded");
			}
			$this->server->getLogger()->notice("Upgrading world \"$name\" to new format. This may take a while.");

			$converter = new FormatConverter($provider, WorldProviderManager::getDefault(), $this->server->getDataPath() . "world_conversion_backups", $this->server->getLogger());
			$provider = $converter->execute();

			$this->server->getLogger()->notice("Upgraded world \"$name\" to new format successfully. Backed up pre-conversion world at " . $converter->getBackupPath());
		}

		$world = new World($this->server, $name, $provider);

		$this->worlds[$world->getId()] = $world;
		$world->setAutoSave($this->autoSave);

		(new WorldLoadEvent($world))->call();

		return true;
	}

	/**
	 * Generates a new world if it does not exist
	 *
	 * @param string   $name
	 * @param int|null $seed
	 * @param string   $generator Class name that extends pocketmine\world\generator\Generator
	 * @param array    $options
	 * @param bool     $backgroundGeneration
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function generateWorld(string $name, ?int $seed = null, string $generator = Normal::class, array $options = [], bool $backgroundGeneration = true) : bool{
		if(trim($name) === "" or $this->isWorldGenerated($name)){
			return false;
		}

		$seed = $seed ?? random_int(INT32_MIN, INT32_MAX);

		Utils::testValidInstance($generator, Generator::class);

		$providerClass = WorldProviderManager::getDefault();

		$path = $this->server->getDataPath() . "worlds/" . $name . "/";
		/** @var WritableWorldProvider $providerClass */
		$providerClass::generate($path, $name, $seed, $generator, $options);

		/** @see WritableWorldProvider::__construct() */
		$world = new World($this->server, $name, new $providerClass($path));
		$this->worlds[$world->getId()] = $world;

		$world->setAutoSave($this->autoSave);

		(new WorldInitEvent($world))->call();

		(new WorldLoadEvent($world))->call();

		if($backgroundGeneration){
			$this->server->getLogger()->notice($this->server->getLanguage()->translateString("pocketmine.level.backgroundGeneration", [$name]));

			$spawnLocation = $world->getSpawnLocation();
			$centerX = $spawnLocation->getFloorX() >> 4;
			$centerZ = $spawnLocation->getFloorZ() >> 4;

			$order = [];

			for($X = -3; $X <= 3; ++$X){
				for($Z = -3; $Z <= 3; ++$Z){
					$distance = $X ** 2 + $Z ** 2;
					$chunkX = $X + $centerX;
					$chunkZ = $Z + $centerZ;
					$index = World::chunkHash($chunkX, $chunkZ);
					$order[$index] = $distance;
				}
			}

			asort($order);

			foreach($order as $index => $distance){
				World::getXZ($index, $chunkX, $chunkZ);
				$world->populateChunk($chunkX, $chunkZ, true);
			}
		}

		return true;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isWorldGenerated(string $name) : bool{
		if(trim($name) === ""){
			return false;
		}
		$path = $this->server->getDataPath() . "worlds/" . $name . "/";
		if(!($this->getWorldByName($name) instanceof World)){
			return !empty(WorldProviderManager::getMatchingProviders($path));
		}

		return true;
	}

	/**
	 * Searches all worlds for the entity with the specified ID.
	 * Useful for tracking entities across multiple worlds without needing strong references.
	 *
	 * @param int $entityId
	 *
	 * @return Entity|null
	 */
	public function findEntity(int $entityId) : ?Entity{
		foreach($this->worlds as $world){
			assert(!$world->isClosed());
			if(($entity = $world->getEntity($entityId)) instanceof Entity){
				return $entity;
			}
		}

		return null;
	}


	public function tick(int $currentTick) : void{
		foreach($this->worlds as $k => $world){
			if(!isset($this->worlds[$k])){
				// World unloaded during the tick of a world earlier in this loop, perhaps by plugin
				continue;
			}

			$worldTime = microtime(true);
			$world->doTick($currentTick);
			$tickMs = (microtime(true) - $worldTime) * 1000;
			$world->tickRateTime = $tickMs;
			if($tickMs >= 50){
				$this->server->getLogger()->debug(sprintf("World \"%s\" took too long to tick: %gms (%g ticks)", $world->getDisplayName(), $tickMs, round($tickMs / 50, 2)));
			}
		}

		if($this->autoSave and ++$this->autoSaveTicker >= $this->autoSaveTicks){
			$this->autoSaveTicker = 0;
			$this->server->getLogger()->debug("[Auto Save] Saving worlds...");
			$start = microtime(true);
			$this->doAutoSave();
			$time = microtime(true) - $start;
			$this->server->getLogger()->debug("[Auto Save] Save completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
		}
	}


	/**
	 * @return bool
	 */
	public function getAutoSave() : bool{
		return $this->autoSave;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave(bool $value) : void{
		$this->autoSave = $value;
		foreach($this->worlds as $world){
			$world->setAutoSave($this->autoSave);
		}
	}

	/**
	 * Returns the period in ticks after which loaded worlds will be automatically saved to disk.
	 *
	 * @return int
	 */
	public function getAutoSaveInterval() : int{
		return $this->autoSaveTicks;
	}

	/**
	 * @param int $autoSaveTicks
	 */
	public function setAutoSaveInterval(int $autoSaveTicks) : void{
		if($autoSaveTicks <= 0){
			throw new \InvalidArgumentException("Autosave ticks must be positive");
		}
		$this->autoSaveTicks = $autoSaveTicks;
	}

	private function doAutoSave() : void{
		Timings::$worldSaveTimer->startTiming();
		foreach($this->worlds as $world){
			foreach($world->getPlayers() as $player){
				if($player->spawned){
					$player->save();
				}
			}
			$world->save(false);
		}
		Timings::$worldSaveTimer->stopTiming();
	}
}
