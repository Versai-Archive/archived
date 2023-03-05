<?php

namespace Skyblock;

require dirname(__FILE__, 2) . '/vendor/autoload.php';

use customiesdevs\customies\item\CustomiesItemFactory;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use Skyblock\Commands\Basic\CraftCommand;
use Skyblock\Commands\Basic\StatCommand;
use Skyblock\Commands\Island\IslandCommand;
use Skyblock\Database\Database;
use Skyblock\InvMenus\CraftingTableInvMenuType;
use Skyblock\Items\Facebook;
use Skyblock\Listener\EventListener;
use Skyblock\Scoreboard\ScoreboardTask;
use Skyblock\Sessions\SessionManager;
use Skyblock\Translator\Translator;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

use Medoo\Medoo;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase {

	use SingletonTrait;

	private Medoo $database;

	private Database $dbutil;

	private SessionManager $sesmng;

	public function onEnable(): void {

		self::setInstance($this);

		Server::getInstance()->getLogger()->info("Server is online! - §l§c" . Server::getInstance()->getPort());

		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}

		InvMenuHandler::getTypeRegistry()->register("portable:crafting", new CraftingTableInvMenuType());

		$this->getServer()->getCommandMap()->register("skyblock", new CraftCommand($this, "craft", Translator::translate("commands.craft.description")));
		$this->getServer()->getCommandMap()->register("skyblock", new IslandCommand($this, "island", Translator::translate("commands.island.description")));
		$this->getServer()->getCommandMap()->register("skyblock", new StatCommand($this, "stat", Translator::translate("commands.stat.description")));
		$this->getScheduler()->scheduleRepeatingTask(new ScoreboardTask(), 20);
		CustomiesItemFactory::getInstance()->registerItem(Facebook::class, "custom:facebook", "Facebook");

		$this->database = new Medoo([
			'type' => 'mysql',
			'host' => $this->getConfig()->getNested("database.host"),
			'database' => $this->getConfig()->getNested("database.database"),
			'username' => $this->getConfig()->getNested("database.user"),
			'password' => $this->getConfig()->getNested("database.password")
		]);

		$this->dbutil = new Database();

		$this->dbutil->initTables();

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		$this->sesmng = new SessionManager($this);
	}

	public function onDisable(): void {

	}

	public function getDirectDatabase(): Medoo {
		return $this->database;
	}

	public function getDatabase(): Database {
		return $this->dbutil;
	}

	public function getSessionManager(): SessionManager {
		return $this->sesmng;
	}

	public function registerEntity(string $class) {
		EntityFactory::getInstance()->register($class, function (World $world, CompoundTag $nbt) use ($class): Entity {
			return new $class(EntityDataHelper::parseLocation($nbt, $world));
		}, [$class]);
	}
}