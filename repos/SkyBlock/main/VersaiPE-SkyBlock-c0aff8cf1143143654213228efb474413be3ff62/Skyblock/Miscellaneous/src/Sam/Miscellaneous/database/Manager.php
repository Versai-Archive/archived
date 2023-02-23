<?php

namespace Sam\Miscellaneous\database;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Manager{
	private DataConnector $database;

	public function __construct($plugin){
		$this->database = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
			"mysql" => "mysql.sql"
		]);
	}

	public function close() : void{
		$this->database->close();
	}

	public function addNewPlayer(string $uuid, string $name) : void{
		$this->database->executeInsert("player.insert", ["uuid" => $uuid, "name" => $name]);
	}

	public function getPlayerID(string $uuid, callable $callback, string $name = null) : void{
		$this->database->executeSelect("player.select", ["uuid" => $uuid], function(array $rows) use ($name, $uuid, $callback){
			if(count($rows) > 0) {
				$id = $rows[0]["id"];
				$callback($id);
			}
			else {
				$this->addNewPlayer($uuid, $name);
			}
		});
	}
}