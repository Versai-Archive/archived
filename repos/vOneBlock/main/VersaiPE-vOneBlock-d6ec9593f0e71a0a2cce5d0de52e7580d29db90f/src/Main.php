<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock;
use Versai\OneBlock\OneBlock\OneBlockManager;

require dirname(__FILE__, 2) . "/vendor/autoload.php";

use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\plugin\PluginBase;
use Medoo\Medoo;
use pocketmine\utils\SingletonTrait;
use Versai\OneBlock\BossBar\DiverseBossBar;
use Versai\OneBlock\Commands\Developer\BugCommand;
use Versai\OneBlock\Commands\Developer\DumpCommand;
use Versai\OneBlock\Commands\Economy\SellCommand;
use Versai\OneBlock\Commands\Economy\StatsCommand;
use Versai\OneBlock\Commands\ShopCommand;
use Versai\OneBlock\Database\Database;
use Versai\OneBlock\Discord\Webhook;
use Versai\OneBlock\Listener\EventListener;

# Managers
use Versai\OneBlock\Scoreboard\ScoreboardManager;
use Versai\OneBlock\Sessions\SessionManager;

# Commands
use Versai\OneBlock\Commands\Island\IslandCommand;

# Tasks
use Versai\OneBlock\Tasks\DisplayTask;
use Versai\OneBlock\Tasks\SavePlayerDataTask;
use Versai\OneBlock\Tasks\BlockParticleTask;

class Main extends PluginBase {

	use SingletonTrait;

	public Database $database;

	public SessionManager $sessionManager;

	public Webhook $whaleWebhook;

	public Webhook $bugWebhook;

	public DiverseBossBar $diverseBossBar;

	public OneBlockManager $islandManager;

	public function onEnable(): void {
		self::setInstance($this);

		$this->saveResource("translations.yml");
		$this->saveResource("shop.yml");
		$this->saveResource("permissions.yml");

		$this->database = new Database(new Medoo([
			'type' => 'mysql',
			'host' => $this->getConfig()->getNested("database.host"),
			'database' => $this->getConfig()->getNested("database.schema"),
			'username' => $this->getConfig()->getNested("database.user"),
			'password' => $this->getConfig()->getNested("database.password")
		]));

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		$this->getScheduler()->scheduleRepeatingTask(new SavePlayerDataTask(), 20*60*2); // 2 minutes
		$this->getScheduler()->scheduleRepeatingTask(new DisplayTask(), 20);
		$this->getScheduler()->scheduleRepeatingTask(new BlockParticleTask(), 20);

		$this->sessionManager = new SessionManager($this);

		$this->islandManager = new OneBlockManager($this);

		$this->whaleWebhook = new Webhook($this->getConfig()->getNested("discord.webhooks.whale.url"));
		$this->bugWebhook = new Webhook($this->getConfig()->getNested("discord.webhooks.bug.url"));

		$this->diverseBossBar = (new DiverseBossBar())->setColor(BossBarColor::RED);

		$this->database->initTables();
		$this->initCommands();
	}

	public function getDatabase(): Database {
		return $this->database;
	}

	public function getSessionManager(): SessionManager {
		return $this->sessionManager;
	}

	public function initCommands(): void {
		$this->getServer()->getCommandMap()->register("oneblock", new IslandCommand($this, "island"));
		$this->getServer()->getCommandMap()->register("oneblock", new SellCommand($this, "sell"));
		$this->getServer()->getCommandMap()->register("oneblock", new ShopCommand($this, "shop"));
		$this->getServer()->getCommandMap()->register("oneblock", new BugCommand($this, "bug"));
		$this->getServer()->getCommandMap()->register("oneblock", new DumpCommand($this, "dump"));
		$this->getServer()->getCommandMap()->register("oneblock", new StatsCommand($this, "estats"));
	}

	public function getWhaleWebhook(): Webhook {
		return $this->whaleWebhook;
	}

	public function getBugWebhook(): Webhook {
		return $this->bugWebhook;
	}

	public function getBossBar(): DiverseBossBar {
		return $this->diverseBossBar;
	}

	public function getIslandManager(): OneBlockManager {
		return $this->islandManager;
	}
}