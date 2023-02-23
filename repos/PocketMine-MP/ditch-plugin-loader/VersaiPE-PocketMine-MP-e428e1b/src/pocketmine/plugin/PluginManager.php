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

use InvalidStateException;
use MJS\TopSort\Implementations\ArraySort;
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
use function array_intersect;
use function array_map;
use function array_pad;
use function array_reverse;
use function count;
use function dirname;
use function explode;
use function file_exists;
use function get_class;
use function implode;
use function is_dir;
use function is_subclass_of;
use function iterator_to_array;
use function mkdir;
use function shuffle;
use function stripos;
use function strpos;
use function strtolower;
use function strtoupper;
use function var_dump;
use const DIRECTORY_SEPARATOR;

/**
 * Manages all the plugins
 */
class PluginManager{

	/** @var Server */
	private $server;

	/** @var PluginFoetus[][] */
	private $foeti = [];
	/** @var PluginLoadOrder[] */
	private $foetusOrders = [];
	/**
	 * @var Plugin[]
	 */
	private $plugins = [];

	private $sorted = false;

	/** @var string|null */
	private $pluginDataDirectory;
	/** @var PluginGraylist|null */
	private $graylist;

	/**
	 * @param Server              $server
	 * @param null|string         $pluginDataDirectory
	 * @param PluginGraylist|null $graylist
	 */
	public function __construct(Server $server, ?string $pluginDataDirectory, ?PluginGraylist $graylist = null){
		$this->server = $server;
		$this->pluginDataDirectory = $pluginDataDirectory;
		if($this->pluginDataDirectory !== null){
			if(!file_exists($this->pluginDataDirectory)){
				@mkdir($this->pluginDataDirectory, 0777, true);
			}elseif(!is_dir($this->pluginDataDirectory)){
				throw new \RuntimeException("Plugin data path $this->pluginDataDirectory exists and is not a directory");
			}
		}

		$this->graylist = $graylist;
	}

	public function getServer() : Server{
		return $this->server;
	}

	/**
	 * @param string $name
	 *
	 * @return null|Plugin
	 */
	public function getPlugin(string $name) : ?Plugin{
		if(isset($this->plugins[$name])){
			return $this->plugins[$name];
		}

		return null;
	}

	/**
	 * @return Plugin[]
	 */
	public function getPlugins() : array{
		return $this->plugins;
	}

	public function getDataDirectory(string $pluginPath, string $pluginName) : string{
		if($this->pluginDataDirectory !== null){
			return $this->pluginDataDirectory . $pluginName;
		}
		return dirname($pluginPath) . DIRECTORY_SEPARATOR . $pluginName;
	}


	/**
	 * @param PluginDescription $description
	 * @param string            $dataFolder
	 * @param callable          $createInstance
	 *
	 * @return Plugin|null
	 */
	public function loadPlugin(PluginDescription $description, string $dataFolder, callable $createInstance) : bool{
		if($this->sorted){
			throw new InvalidStateException("Plugins calling PluginManager->loadPlugin() must use \"load: SCAN\"");
		}

		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.load", [$description->getFullName()]));
		if(!$this->checkRequirements($description)){
			return false;
		}

		$pluginName = $description->getName();
		if(file_exists($dataFolder) and !is_dir($dataFolder)){
			$this->server->getLogger()->error("Projected dataFolder '$dataFolder' for $pluginName exists and is not a directory");
			return false;
		}
		if(!file_exists($dataFolder)){
			mkdir($dataFolder, 0777, true);
		}

		$foetus = new PluginFoetus($description, $dataFolder, $createInstance);

		$order = $description->getOrder();

		if(isset($this->foetusOrders[$pluginName])){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.duplicateError"
				, [$pluginName]));
			return false;
		}
		$this->foetusOrders[$pluginName] = $order;

		if(!isset($this->foeti[$order->getEnumName()])){
			$this->foeti[$order->getEnumName()] = [];
		}
		$this->foeti[$order->getEnumName()][$pluginName] = $foetus;

		return true;
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
		$serverApi = array_pad(explode("-", $serverString, 2), 2, "");
		$serverNumbers = array_map("\intval", explode(".", $serverApi[0]));

		foreach($versions as $version){
			//Format: majorVersion.minorVersion.patch (3.0.0)
			//    or: majorVersion.minorVersion.patch-devBuild (3.0.0-alpha1)
			if($version !== $serverString){
				$pluginApi = array_pad(explode("-", $version, 2), 2, ""); //0 = version, 1 = suffix (optional)

				if(strtoupper($pluginApi[1]) !== strtoupper($serverApi[1])){ //Different release phase (alpha vs. beta) or phase build (alpha.1 vs alpha.2)
					continue;
				}

				$pluginNumbers = array_map("\intval", array_pad(explode(".", $pluginApi[0]), 3, "0")); //plugins might specify API like "3.0" or "3"

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

	private function checkRequirements(PluginDescription $description) : bool{
		try{
			$description->checkRequiredExtensions();
		}catch(PluginException $ex){
			$this->server->getLogger()->error($ex->getMessage());
			return false;
		}

		$name = $description->getName();
		foreach(["pocketmine", "minecraft", "mojang"] as $taboo){
			if(stripos($name, $taboo) !== false){
				$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
					$name, "%pocketmine.plugin.restrictedName"
				]));
				return false;
			}
		}
		if(strpos($name, " ") !== false){
			$this->server->getLogger()->warning($this->server->getLanguage()->translateString("
pocketmine.plugin.spacesDiscouraged", [$name]));
		}
		if($this->graylist !== null and !$this->graylist->isAllowed($name)){
			$this->server->getLogger()->notice($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
				$name,
				"Disallowed by graylist"
			]));
			return false;
		}

		if(!ApiVersion::isCompatible($this->server->getApiVersion(), $description->getCompatibleApis())){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
				$name,
				$this->server->getLanguage()->translateString("%pocketmine.plugin.incompatibleAPI", [implode(", ", $description->
				getCompatibleApis())])
			]));
			return false;
		}
		$ambiguousVersions = ApiVersion::checkAmbiguousVersions($description->getCompatibleApis());
		if(!empty($ambiguousVersions)){
			$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
				$name,
				$this->server->getLanguage()->translateString("pocketmine.plugin.ambiguousMinAPI", [implode(", ",
					$ambiguousVersions)])
			]));
			return false;
		}

		if(count($pluginMcpeProtocols = $description->getCompatibleMcpeProtocols()) > 0){
			$serverMcpeProtocols = [ProtocolInfo::CURRENT_PROTOCOL];
			if(count(array_intersect($pluginMcpeProtocols, $serverMcpeProtocols)) === 0){
				$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
					$name,
					$this->server->getLanguage()->translateString("%pocketmine.plugin.incompatibleProtocol", [implode(", ",
						$pluginMcpeProtocols)])
				]));
				return false;
			}
		}

		if($description->getOrder() === PluginLoadOrder::SCAN() && count($description->getDepend()) + count($description->getSoftDepend()) + count($description->getLoadBefore()) > 0){
			$this->server->getLogger()->error("Plugins at SCAN level must not depend/softDepend/loadBefore anything"); // TODO multilang
			return false;
		}

		return true;
	}

	public function sortPlugins() : void{
		$history = [];

		foreach($this->foeti as $order => $plugins){
			foreach($plugins as $name => $plugin){
				if(isset($history[$name])){
					$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.duplicateError"
						, [$name])); // TODO fix error message
					unset($plugins[$name]); // not loading the later one
					continue;
				}
				$history[$name] = true;
			}

			if($order === PluginLoadOrder::SCAN()->getEnumName()){
				// put this after $history so as to detect duplicates with SCAN()
				continue;
			}

			foreach($plugins as $name => $plugin){
				foreach($plugin->getDescription()->getDepend() as $depend){
					if(!isset($this->foetusOrders[$depend])){
						$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
							$name,
							$this->server->getLanguage()->translateString("%pocketmine.plugin.unknownDependency", [$depend])
						]));
						unset($plugins[$name]); // not loading the child as parent is unresolved
						continue;
					}
				}
			}

			$sorted = self::topoSort($plugins);
			$this->foeti[$order] = $sorted;
		}

		$this->sorted = true;
	}

	/**
	 * @param PluginFoetus[] $plugins
	 *
	 * @return array
	 */
	private static function topoSort(array $plugins) : array{
		$deps = [];
		$shuffled = $plugins;
		shuffle($shuffled); // to remind plugins to handle dependencies properly
		foreach($shuffled as $plugin){
			$name = $plugin->getDescription()->getName(); // shuffle() does not preserve keys
			if(!isset($deps[$name])){
				$deps[$name] = [];
			}
			foreach($plugin->getDescription()->getDepend() as $dep){
				$deps[$name][] = $dep;
			}
			foreach($plugin->getDescription()->getSoftDepend() as $dep){
				$deps[$name][] = $dep;
			}
			foreach($plugin->getDescription()->getLoadBefore() as $before){
				if(!isset($deps[$before])){
					$deps[$before] = [];
				}
				$deps[$before][] = $name;
			}

		}

		$sort = new ArraySort();
		$sort->set($deps);
		$sorted = $sort->sort();

		$result = [];
		foreach($sorted as $name){
			$result[$name] = $plugins[$name];
		}
		return $result;
	}

	/**
	 * @param PluginLoadOrder $type
	 */
	public function enablePlugins(PluginLoadOrder $type) : void{
		foreach($this->foeti[$type->getEnumName()] ?? [] as $foetus){
			$this->enablePlugin($foetus);
		}
	}

	public function enablePlugin(PluginFoetus $foetus) : void{
		$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.enable", [$foetus->getDescription()->getFullName()]));

		$permManager = PermissionManager::getInstance();
		foreach($foetus->getDescription()->getPermissions() as $perm){
			$permManager->addPermission($perm);
		}
		$plugin = $foetus->callCreateInstance();
		$plugin->getScheduler()->setEnabled(true);
		$plugin->onEnableStateChange(true);

		$this->plugins[$plugin->getDescription()->getName()] = $plugin;

		(new PluginEnableEvent($plugin))->call();
	}

	public function disablePlugins() : void{
		foreach(array_reverse($this->getPlugins()) as $plugin){ // First-In-Last-Out to fulfill dependency requirements
			$this->disablePlugin($plugin);
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function disablePlugin(Plugin $plugin) : void{
		if($plugin->isEnabled()){
			// TODO remove
			$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.disable", [$plugin->getDescription()->getFullName()]));
			(new PluginDisableEvent($plugin))->call();

			$plugin->onEnableStateChange(false);
			$plugin->getScheduler()->shutdown();
			HandlerList::unregisterAll($plugin);
			$permManager = PermissionManager::getInstance();
			foreach($plugin->getDescription()->getPermissions() as $perm){
				$permManager->removePermission($perm);
			}
		}
	}

	public function tickSchedulers(int $currentTick) : void{
		foreach($this->plugins as $p){
			$p->getScheduler()->mainThreadHeartbeat($currentTick);
		}
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

				$handleCancelled = false;
				if(isset($tags["handleCancelled"])){
					switch(strtolower($tags["handleCancelled"])){
						case "true":
						case "":
							$handleCancelled = true;
							break;
						case "false":
							break;
						default:
							throw new PluginException("Event handler " . Utils::getNiceClosureName($handlerClosure) . "() declares invalid @handleCancelled value \"" . $tags["handleCancelled"] . "\"");
					}
				}

				$this->registerEvent($eventClass->getName(), $handlerClosure, $priority, $plugin, $handleCancelled);
			}
		}
	}

	/**
	 * @param string   $event Class name that extends Event
	 * @param \Closure $handler
	 * @param int      $priority
	 * @param Plugin   $plugin
	 * @param bool     $handleCancelled
	 *
	 * @throws \ReflectionException
	 */
	public function registerEvent(string $event, \Closure $handler, int $priority, Plugin $plugin, bool $handleCancelled = false) : void{
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

		$this->getEventListeners($event)->register(new RegisteredListener($handler, $priority, $plugin, $handleCancelled, $timings));
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
