<?php

declare(strict_types=1);

namespace lastseen;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use lastseen\libs\poggit\libasynql\DataConnector;
use lastseen\libs\poggit\libasynql\libasynql;
use lastseen\libs\poggit\libasynql\SqlError;

class Loader extends PluginBase implements Listener{

	public const PERMISSION = "lastseen.command";

	/** @var DataConnector */
	public $database;

	public function onEnable() : void{
		$this->database = libasynql::create($this, [
			"type" => "sqlite",
			"sqlite" => [
				"file" => $this->getDataFolder() . "Players.db"
			],
			"worker-limit" => 1,
		], [
			"sqlite" => "sqlite_stmts.sql",
		]);

		$this->database->executeGeneric("lastseen.init");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->register("ls", new LastSeenCommand("ls", $this));

		$this->database->waitAll();
	}

	public function onDisable(){
		$this->database->waitAll();
		$this->database->close();
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$this->registerPlayer($player->getName(), function(string $name) : void{
			$this->getLogger()->info(TextFormat::AQUA . "[LastSeen] " . $name . " has been registered!");
		});
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		$this->setTime($player->getName(), date("l,F,Y | G:i:s [T]"));
	}

	/**
	 * @param string $username
	 * @param callable $onSuccess
	 */
	public function registerPlayer(string $username, callable $onSuccess) : void{
		Utils::validateCallableSignature($onSuccess, static function(?string $time) : void {});
		$this->getTime($username, function(?string $time) use ($username, $onSuccess) : void{
			if($time !== null){
				return;
			}

			$this->database->executeInsert("lastseen.time.register", [
				"username" => $username,
				"time" => "0"
			]);

			$onSuccess($username);
		});
	}

	/**
	 * @param string $username
	 * @param string $time
	 */
	public function setTime(string $username, string $time){
		$this->database->executeChange("lastseen.time.set", [
			"username" => $username,
			"time" => $time,
		], null, function(SqlError $error) : void{
			$this->getLogger()->debug($error->getErrorMessage());
		});
	}

	/**
	 * @param string $username
	 * @param callable $onSuccess a callback which receives the time,
	 * @param callable|null $onError an optional callback when the query has failed
	 */
	public function getTime(string $username, callable $onSuccess, ?callable $onError = null){
		Utils::validateCallableSignature($onSuccess, static function(?string $time) : void {});
		$this->database->executeSelect("lastseen.time.get", [
			"username" => $username,
		], static function(array $rows) use($onSuccess) : void{
			if(isset($rows[0]["time"])){
				$onSuccess($rows[0]["time"]);
			}

			$onSuccess(null);
		}, $onError);
	}
}