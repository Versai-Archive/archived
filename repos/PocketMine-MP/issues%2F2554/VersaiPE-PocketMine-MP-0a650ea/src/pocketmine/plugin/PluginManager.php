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

namespace pocketmine\plugin;

use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerList;
use pocketmine\event\Listener;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\Utils;

/**
 * Manages all the plugins
 */
class PluginManager{

	/** @var Server */
	private $server;

	/**
	 * @var Plugin[]
	 */
	protected $plugins = [];

	/**
	 * @var Plugin[]
	 */
	protected $enabledPlugins = [];

	/**
	 * @var PluginLoader[]
	 */
	protected $fileAssociations = [];

	/** @var string|null */
	private $pluginDataDirectory;

	/**
	 * @param Server      $server
	 * @param null|string $pluginDataDirectory
	 */
	public function __construct(Server $server, ?string $pluginDataDirectory){
		$this->server = $server;
		$this->pluginDataDirectory = $pluginDataDirectory;
		if($this->pluginDataDirectory !== null){
			if(!file_exists($this->pluginDataDirectory)){
				@mkdir($this->pluginDataDirectory, 0777, true);
			}elseif(!is_dir($this->pluginDataDirectory)){
				throw new \RuntimeException("Plugin data path $this->pluginDataDirectory exists and is not a directory");
			}
		}
	}

	/**
	 * @param string $name
	 *
	 * @return null|Plugin
	 */
	public function getPlugin(string $name){
		if(isset($this->plugins[$name])){
			return $this->plugins[$name];
		}

		return null;
	}

	/**
	 * @param PluginLoader $loader
	 */
	public function registerInterface(PluginLoader $loader) : void{
		$this->fileAssociations[get_class($loader)] = $loader;
	}

	/**
	 * @return Plugin[]
	 */
	public function getPlugins() : array{
		return $this->plugins;
	}

	private function getDataDirectory(string $pluginPath, string $pluginName) : string{
		if($this->pluginDataDirectory !== null){
			return $this->pluginDataDirectory . $pluginName;
		}
		return dirname($pluginPath) . DIRECTORY_SEPARATOR . $pluginName;
	}

	/**
	 * @param string         $path
	 * @param PluginLoader[] $loaders
	 *
	 * @return Plugin|null
	 */
	public function loadPlugin(string $path, array $loaders = null) : ?Plugin{
		foreach($loaders ?? $this->fileAssociations as $loader){
			if($loader->canLoadPlugin($path)){
				$description = $loader->getPluginDescription($path);
				if($description instanceof PluginDescription){
					$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.load", [$description->getFullName()]));
					try{
						$description->checkRequiredExtensions();
					}catch(PluginException $ex){
						$this->server->getLogger()->error($ex->getMessage());
						return null;
					}

					$dataFolder = $this->getDataDirectory($path, $description->getName());
					if(file_exists($dataFolder) and !is_dir($dataFolder)){
						$this->server->getLogger()->error("Projected dataFolder '" . $dataFolder . "' for " . $description->getName() . " exists and is not a directory");
						return null;
					}
					if(!file_exists($dataFolder)){
						mkdir($dataFolder, 0777, true);
					}

					$prefixed = $loader->getAccessProtocol() . $path;
					$loader->loadPlugin($prefixed);

					$mainClass = $description->getMain();
					if(!class_exists($mainClass, true)){
						$this->server->getLogger()->error("Main class for plugin " . $description->getName() . " not found");
						return null;
					}
					if(!is_a($mainClass, Plugin::class, true)){
						$this->server->getLogger()->error("Main class for plugin " . $description->getName() . " is not an instance of " . Plugin::class);
						return null;
					}

					try{
						/**
						 * @var Plugin $plugin
						 * @see Plugin::__construct()
						 */
						$plugin = new $mainClass($loader, $this->server, $description, $dataFolder, $prefixed);
						$this->plugins[$plugin->getDescription()->getName()] = $plugin;

						return $plugin;
					}catch(\Throwable $e){
						$this->server->getLogger()->logException($e);
						return null;
					}
				}
			}
		}

		return null;
	}

	/**
	 * @param string $directory
	 * @param array  $newLoaders
	 *
	 * @return Plugin[]
	 */
	public function loadPlugins(string $directory, array $newLoaders = null){
		if(!is_dir($directory)){
			return [];
		}

		$plugins = [];
		$loadedPlugins = [];
		$dependencies = [];
		$softDependencies = [];
		if(is_array($newLoaders)){
			$loaders = [];
			foreach($newLoaders as $key){
				if(isset($this->fileAssociations[$key])){
					$loaders[$key] = $this->fileAssociations[$key];
				}
			}
		}else{
			$loaders = $this->fileAssociations;
		}
		foreach($loaders as $loader){
			foreach(new \DirectoryIterator($directory) as $file){
				if($file === "." or $file === ".."){
					continue;
				}
				$file = $directory . $file;
				if(!$loader->canLoadPlugin($file)){
					continue;
				}
				try{
					$description = $loader->getPluginDescription($file);
					if($description === null){
						continue;
					}

					$name = $description->getName();
					if(stripos($name, "pocketmine") !== false or stripos($name, "minecraft") !== false or stripos($name, "mojang") !== false){
						$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.restrictedName"]));
						continue;
					}elseif(strpos($name, " ") !== false){
						$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.plugin.spacesDiscouraged", [$name]));
					}

					if(isset($plugins[$name]) or $this->getPlugin($name) instanceof Plugin){
						$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.duplicateError", [$name]));
						continue;
					}

					if(!$this->isCompatibleApi(...$description->getCompatibleApis())){
						$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
							$name,
							$this->server->getLanguage()->translateString("%pocketmine.plugin.incompatibleAPI", [implode(", ", $description->getCompatibleApis())])
						]));
						continue;
					}

					if(count($pluginMcpeProtocols = $description->getCompatibleMcpeProtocols()) > 0){
						$serverMcpeProtocols = [ProtocolInfo::CURRENT_PROTOCOL];
						if(count(array_intersect($pluginMcpeProtocols, $serverMcpeProtocols)) === 0){
							$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
								$name,
								$this->server->getLanguage()->translateString("%pocketmine.plugin.incompatibleProtocol", [implode(", ", $pluginMcpeProtocols)])
							]));
							continue;
						}
					}

					$plugins[$name] = $file;

					$softDependencies[$name] = $description->getSoftDepend();
					$dependencies[$name] = $description->getDepend();

					foreach($description->getLoadBefore() as $before){
						if(isset($softDependencies[$before])){
							$softDependencies[$before][] = $name;
						}else{
							$softDependencies[$before] = [$name];
						}
					}
				}catch(\Throwable $e){
					$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.fileError", [$file, $directory, $e->getMessage()]));
					$this->server->getLogger()->logException($e);
				}
			}
		}


		while(count($plugins) > 0){
			$missingDependency = true;
			foreach($plugins as $name => $file){
				if(isset($dependencies[$name])){
					foreach($dependencies[$name] as $key => $dependency){
						if(isset($loadedPlugins[$dependency]) or $this->getPlugin($dependency) instanceof Plugin){
							unset($dependencies[$name][$key]);
						}elseif(!isset($plugins[$dependency])){
							$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
								$name,
								$this->server->getLanguage()->translateString("%pocketmine.plugin.unknownDependency", [$dependency])
							]));
							unset($plugins[$name]);
							continue 2;
						}
					}

					if(count($dependencies[$name]) === 0){
						unset($dependencies[$name]);
					}
				}

				if(isset($softDependencies[$name])){
					foreach($softDependencies[$name] as $key => $dependency){
						if(isset($loadedPlugins[$dependency]) or $this->getPlugin($dependency) instanceof Plugin){
							unset($softDependencies[$name][$key]);
						}
					}

					if(count($softDependencies[$name]) === 0){
						unset($softDependencies[$name]);
					}
				}

				if(!isset($dependencies[$name]) and !isset($softDependencies[$name])){
					unset($plugins[$name]);
					$missingDependency = false;
					if($plugin = $this->loadPlugin($file, $loaders) and $plugin instanceof Plugin){
						$loadedPlugins[$name] = $plugin;
					}else{
						$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.genericLoadError", [$name]));
					}
				}
			}

			if($missingDependency){
				foreach($plugins as $name => $file){
					if(!isset($dependencies[$name])){
						unset($softDependencies[$name]);
						unset($plugins[$name]);
						$missingDependency = false;
						if($plugin = $this->loadPlugin($file, $loaders) and $plugin instanceof Plugin){
							$loadedPlugins[$name] = $plugin;
						}else{
							$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.genericLoadError", [$name]));
						}
					}
				}

				//No plugins loaded :(
				if($missingDependency){
					foreach($plugins as $name => $file){
						$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.circularDependency"]));
					}
					$plugins = [];
				}
			}
		}

		return $loadedPlugins;
	}

	/**
	 * Returns whether a specified API version string is considered compatible with the server's API version.
	 *
	 * @param string ...$versions
	 *
	 * @return bool
	 */
	public function isCompatibleApi(string ...$versions) : bool{
		$serverString = $this->server->getApiVersion();
		$serverApi = array_pad(explode("-", $serverString), 2, "");
		$serverNumbers = array_map("intval", explode(".", $serverApi[0]));

		foreach($versions as $version){
			//Format: majorVersion.minorVersion.patch (3.0.0)
			//    or: majorVersion.minorVersion.patch-devBuild (3.0.0-alpha1)
			if($version !== $serverString){
				$pluginApi = array_pad(explode("-", $version), 2, ""); //0 = version, 1 = suffix (optional)

				if(strtoupper($pluginApi[1]) !== strtoupper($serverApi[1])){ //Different release phase (alpha vs. beta) or phase build (alpha.1 vs alpha.2)
					continue;
				}

				$pluginNumbers = array_map("intval", array_pad(explode(".", $pluginApi[0]), 3, "0")); //plugins might specify API like "3.0" or "3"

				if($pluginNumbers[0] !== $serverNumbers[0]){ //Completely different API version
					continue;
				}

				if($pluginNumbers[1] > $serverNumbers[1]){ //If the plugin requires new API features, being backwards compatible
					continue;
				}

				if($pluginNumbers[1] === $serverNumbers[1] and $pluginNumbers[2] > $serverNumbers[2]){ //If the plugin requires bug fixes in patches, being backwards compatible
					continue;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @return bool
	 */
	public function isPluginEnabled(Plugin $plugin) : bool{
		return isset($this->plugins[$plugin->getDescription()->getName()]) and $plugin->isEnabled();
	}

	/**
	 * @param Plugin $plugin
	 */
	public function enablePlugin(Plugin $plugin){
		if(!$plugin->isEnabled()){
			try{
				$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.enable", [$plugin->getDescription()->getFullName()]));

				$permManager = PermissionManager::getInstance();
				foreach($plugin->getDescription()->getPermissions() as $perm){
					$permManager->addPermission($perm);
				}
				$plugin->getScheduler()->setEnabled(true);
				$plugin->onEnableStateChange(true);

				$this->enabledPlugins[$plugin->getDescription()->getName()] = $plugin;

				(new PluginEnableEvent($plugin))->call();
			}catch(\Throwable $e){
				$this->server->getLogger()->logException($e);
				$this->disablePlugin($plugin);
			}
		}
	}

	public function disablePlugins(){
		foreach($this->getPlugins() as $plugin){
			$this->disablePlugin($plugin);
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function disablePlugin(Plugin $plugin){
		if($plugin->isEnabled()){
			$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.disable", [$plugin->getDescription()->getFullName()]));
			(new PluginDisableEvent($plugin))->call();

			unset($this->enabledPlugins[$plugin->getDescription()->getName()]);

			try{
				$plugin->onEnableStateChange(false);
			}catch(\Throwable $e){
				$this->server->getLogger()->logException($e);
			}
			$plugin->getScheduler()->shutdown();
			HandlerList::unregisterAll($plugin);
			$permManager = PermissionManager::getInstance();
			foreach($plugin->getDescription()->getPermissions() as $perm){
				$permManager->removePermission($perm);
			}
		}
	}

	public function tickSchedulers(int $currentTick) : void{
		foreach($this->enabledPlugins as $p){
			$p->getScheduler()->mainThreadHeartbeat($currentTick);
		}
	}

	public function clearPlugins(){
		$this->disablePlugins();
		$this->plugins = [];
		$this->enabledPlugins = [];
		$this->fileAssociations = [];
	}

	/**
	 * Registers all the events in the given Listener class
	 *
	 * @param Listener $listener
	 * @param Plugin   $plugin
	 *
	 * @throws PluginException
	 */
	public function registerEvents(Listener $listener, Plugin $plugin) : void{
		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin attempted to register " . get_class($listener) . " while not enabled");
		}

		$reflection = new \ReflectionClass(get_class($listener));
		foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
			if(!$method->isStatic() and $method->getDeclaringClass()->implementsInterface(Listener::class)){
				$tags = Utils::parseDocComment((string) $method->getDocComment());
				if(isset($tags["notHandler"])){
					continue;
				}

				$parameters = $method->getParameters();
				if(count($parameters) !== 1){
					continue;
				}

				$handlerClosure = $method->getClosure($listener);

				try{
					$eventClass = $parameters[0]->getClass();
				}catch(\ReflectionException $e){ //class doesn't exist
					if(isset($tags["softDepend"]) && !isset($this->plugins[$tags["softDepend"]])){
						$this->server->getLogger()->debug("Not registering @softDepend listener " . Utils::getNiceClosureName($handlerClosure) . "(" . $parameters[0]->getType()->getName() . ") because plugin \"" . $tags["softDepend"] . "\" not found");
						continue;
					}

					throw $e;
				}
				if($eventClass === null or !$eventClass->isSubclassOf(Event::class)){
					continue;
				}

				try{
					$priority = isset($tags["priority"]) ? EventPriority::fromString($tags["priority"]) : EventPriority::NORMAL;
				}catch(\InvalidArgumentException $e){
					throw new PluginException("Event handler " . Utils::getNiceClosureName($handlerClosure) . "() declares invalid/unknown priority \"" . $tags["priority"] . "\"");
				}

				$ignoreCancelled = false;
				if(isset($tags["ignoreCancelled"])){
					switch(strtolower($tags["ignoreCancelled"])){
						case "true":
						case "":
							$ignoreCancelled = true;
							break;
						case "false":
							$ignoreCancelled = false;
							break;
						default:
							throw new PluginException("Event handler " . Utils::getNiceClosureName($handlerClosure) . "() declares invalid @ignoreCancelled value \"" . $tags["ignoreCancelled"] . "\"");
					}
				}

				$this->registerEvent($eventClass->getName(), $handlerClosure, $priority, $plugin, $ignoreCancelled);
			}
		}
	}

	/**
	 * @param string   $event Class name that extends Event
	 * @param \Closure $handler
	 * @param int      $priority
	 * @param Plugin   $plugin
	 * @param bool     $ignoreCancelled
	 *
	 * @throws \ReflectionException
	 */
	public function registerEvent(string $event, \Closure $handler, int $priority, Plugin $plugin, bool $ignoreCancelled = false) : void{
		if(!is_subclass_of($event, Event::class)){
			throw new PluginException($event . " is not an Event");
		}

		$handlerName = Utils::getNiceClosureName($handler);

		$tags = Utils::parseDocComment((string) (new \ReflectionClass($event))->getDocComment());
		if(isset($tags["deprecated"]) and $this->server->getProperty("settings.deprecated-verbose", true)){
			$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.plugin.deprecatedEvent", [
				$plugin->getName(),
				$event,
				$handlerName
			]));
		}


		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin attempted to register event handler " . $handlerName . "() to event " . $event . " while not enabled");
		}

		$timings = new TimingsHandler("Plugin: " . $plugin->getDescription()->getFullName() . " Event: " . $handlerName . "(" . (new \ReflectionClass($event))->getShortName() . ")");

		$this->getEventListeners($event)->register(new RegisteredListener($handler, $priority, $plugin, $ignoreCancelled, $timings));
	}

	/**
	 * @param string $event
	 *
	 * @return HandlerList
	 */
	private function getEventListeners(string $event) : HandlerList{
		$list = HandlerList::getHandlerListFor($event);
		if($list === null){
			throw new PluginException("Abstract events not declaring @allowHandle cannot be handled (tried to register listener for $event)");
		}
		return $list;
	}
}
