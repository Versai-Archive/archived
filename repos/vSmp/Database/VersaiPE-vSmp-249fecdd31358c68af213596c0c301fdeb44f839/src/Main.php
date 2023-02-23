<?php

declare(strict_types=1);

/**
 * This file is in charge of loading all of the features
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

# TASKS
use Versai\RPGCore\Tasks\DisplayTask;
use Versai\RPGCore\Tasks\ManaRecoverTask;
use Versai\RPGCore\Tasks\SaveDataTask;

# COMMANDS
use Versai\RPGCore\Commands\ManaCommand;
use Versai\RPGCore\Commands\StatCommand;
use Versai\RPGCore\Commands\HelpCommand;

# LISTENERS
use Versai\RPGCore\Listeners\EventListener;
use Versai\RPGCore\Indicators\IndicatorManager;
use Versai\RPGCore\Listeners\LevelListener;
use Versai\RPGCore\Listeners\MobEventListener;
use Versai\RPGCore\Listeners\RegisterPlayer;
use Versai\RPGCore\Quests\QuestListener;

# MANAGERS
use Versai\RPGCore\Entities\EntityManager;

# OTHER
use Versai\RPGCore\Sessions\SessionManager;
use poggit\libasynql\libasynql;
use poggit\libasynql\DataConnector;
use Versai\RPGCore\Data\SQLDataStorer;

class Main extends PluginBase
{
    use SingletonTrait;

	private $database;

	private SessionManager $sessionManager;

	public function onEnable() : void {
	    self::setInstance($this);

		$this->saveDefaultConfig();

		$this->initListener(); # Registers event listeners
		$this->initCommands(); # Registers commands
		$this->initTasks();    # Registers tasks

		$this->saveDefaultConfig();
        $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ]);

		$data = new SQLDataStorer($this);

		$data->initTables();
		
		EntityManager::init(); # Entities\EntityManager.php

		$this->sessionManager = new SessionManager($this);
	}

	public function onDisable(): void {
		if(isset($this->database)) $this->database->close();
	}
	
	function initListener() : void {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new IndicatorManager($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new LevelListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new MobEventListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new RegisterPlayer($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new QuestListener($this), $this);
	}
	
	function initCommands() : void {
	    $commands = [
	        new ManaCommand("mana", "Mana management command"),
            new StatCommand("stat", "Stat management command", $this),
			new HelpCommand("smphelp", "SMP Help command")
        ];
		foreach($commands as $command) {
		    $this->getServer()->getCommandMap()->register("rpgcore", $command);
        }
	}
	
	function initTasks() : void {
		$this->getScheduler()->scheduleRepeatingTask(new DisplayTask($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new ManaRecoverTask($this), 25);
		$this->getScheduler()->scheduleRepeatingTask(new SaveDataTask($this), 20 * 60 * 3);
	}

	public function getSessionManager(): SessionManager {
		return $this->sessionManager;
	}

	public function getDataConnector(): DataConnector {
		return $this->database;
	}
}
