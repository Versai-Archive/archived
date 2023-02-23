<?php

declare(strict_types=1);

/**
 * This file is in charge of loading all of the features
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore;

use alvin0319\CustomItemLoader\items\SMPItemIds;
use alvin0319\CustomItemLoader\items\SMPItemLoader;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

# TASKS
use pocketmine\world\World;
use Versai\RPGCore\Entities\Goblin;
use Versai\RPGCore\Libraries\Translator\Translator;
use Versai\RPGCore\Tasks\DisplayTask;
use Versai\RPGCore\Tasks\ManaRecoverTask;
use Versai\RPGCore\Tasks\SaveDataTask;
use Versai\RPGCore\Tasks\MobTask;
use Versai\RPGCore\Tasks\LineTask;
use Versai\RPGCore\Tasks\Tasks\UpdatePlayerStatTask;

# COMMANDS
use Versai\RPGCore\Commands\ManaCommand;
use Versai\RPGCore\Commands\StatCommand;
use Versai\RPGCore\Commands\HelpCommand;
use Versai\RPGCore\Commands\NPCCommand;
use Versai\RPGCore\Libraries\pathfinder\command\PathfinderCommand;

# LISTENERS
use Versai\RPGCore\Listeners\EventListener;
use Versai\RPGCore\Indicators\IndicatorManager;
use Versai\RPGCore\Listeners\LevelListener;
use Versai\RPGCore\Listeners\MobEventListener;
use Versai\RPGCore\Listeners\RegisterPlayer;
use Versai\RPGCore\Quests\QuestListener;
use Versai\RPGCore\Listeners\NPCListener;

# MANAGERS
use Versai\RPGCore\Entities\EntityManager;

# OTHER
use Versai\RPGCore\Sessions\SessionManager;

use poggit\libasynql\libasynql;
use poggit\libasynql\DataConnector;
use Versai\RPGCore\Data\SQLDataStorer;
use xenialdan\apibossbar\DiverseBossBar;
use Versai\RPGCore\Tasks\SessionQuestUpdaterTask;
use Versai\RPGCore\Tasks\UpdatePlayerStatsTask;
use pocketmine\crafting\CraftingManager;
use pocketmine\crafting\{
	ShapedRecipe,
	ShapelessRecipe
};
use pocketmine\item\VanillaItems;
use alvin0319\CustomItemLoader\CustomItemManager;

class Main extends PluginBase
{
    use SingletonTrait;

	private SessionManager $sessionManager;
	private DiverseBossBar $bossBar;
	

	public function onEnable() : void {
	    self::setInstance($this);

		$this->saveDefaultConfig();

		$this->initListener(); # Registers event listeners
		$this->initCommands(); # Registers commands
		$this->initTasks();    # Registers tasks

		$this->saveDefaultConfig();
		
		EntityManager::init(); # Entities\EntityManager.php

		$this->sessionManager = new SessionManager($this);
		$this->bossBar = new DiverseBossBar();
	}

    function initListener() : void {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new IndicatorManager($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new LevelListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new MobEventListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new RegisterPlayer($this), $this);
		//$this->getServer()->getPluginManager()->registerEvents(new MobEventListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new RegisterPlayer($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new QuestListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new NPCListener($this), $this);
	}
	
	function initCommands() : void {
	    $commands = [
	        new ManaCommand("mana", "Mana management command"),
            new StatCommand("stat", "Stat management command", $this),
			new HelpCommand("smphelp", "SMP Help command", $this),
			new NPCCommand("npc", "summon npc"),
			new PathfinderCommand()
        ];
		foreach($commands as $command) {
		    $this->getServer()->getCommandMap()->register("rpgcore", $command);
        }
	}
	
	function initTasks() : void {
		$this->getScheduler()->scheduleRepeatingTask(new DisplayTask($this), 1);
		$this->getScheduler()->scheduleRepeatingTask(new ManaRecoverTask($this), 25);
		$this->getScheduler()->scheduleRepeatingTask(new SessionQuestUpdaterTask($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new LineTask($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new UpdatePlayerStatsTask($this), 20);
	}

	public function getSessionManager(): SessionManager {
		return $this->sessionManager;
	}

	public function getBossBar(): DiverseBossBar {
        return $this->bossBar;
    }
}

