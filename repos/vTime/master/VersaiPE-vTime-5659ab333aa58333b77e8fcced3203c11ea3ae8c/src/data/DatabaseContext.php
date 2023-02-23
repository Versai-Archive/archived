<?php

namespace Versai\vTime\data;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Versai\vTime\Main;

class DatabaseContext{

	private DataConnector $database;
	private Main $plugin;

	/**
	 * DatabaseContext constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin){
	    $this->plugin = $plugin;
		$this->database = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
			"mysql" => "mysql.sql"
		]);
	}

	public function close(){
		if(isset($this->database)) $this->database->close();
	}

	public function init(){
		$this->database->executeGeneric('vtime.init', [], function(): void{
		    $this->plugin->getLogger()->info("Database loaded!");
        });
	}

	public function onJoin(string $username){
		$current = intval(microtime(true));
		$this->database->executeInsert('vtime.joined', ["username" => $username, "current" => $current]);
	}

	public function onQuit(string $username){
		$current = intval(microtime(true));
		$this->database->executeChange('vtime.update', ["username" => $username, "time" => $current]);
	}

	public function getInfo(string $username, callable $callback){
		$this->database->executeSelect('vtime.get', ["username" => $username], function(array $rows) use ($callback) : void{
			if(isset($rows[0])){
				$callback($rows[0]);
			}
			else $callback(null);
		});
	}

	public function getTop(callable $callback){
		$this->database->executeSelect('vtime.top', [], function(array $rows) use ($callback): void{
			$callback($rows);
		});
	}

	public function deleteAll(callable $callback){
		$this->database->executeGeneric('vtime.deleteall', [], $callback);
	}
}