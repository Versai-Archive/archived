<?php

namespace Sam\VCrates\database;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Manager{
	private DataConnector $database;

	public function __construct($plugin){
		$this->database = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
			"mysql" => "mysql.sql"
		]);
	}

	public function init() : void{
		$this->database->executeGeneric('player.init');
	}

	public function close() : void{
		$this->database->close();
	}

	public function getPlayerKeys(int $id, callable $callback) : void{
		$this->database->executeSelect("keys.select", ["id" => $id], function(array $rows) use ($id, $callback){
			if(count($rows) == 0){
				$this->database->executeInsert("keys.insert", ["id" => $id]);
				$callback([0, 0, 0, 0]);
			}else{
				$callback([$rows[0]["common"], $rows[0]["rare"], $rows[0]["epic"], $rows[0]["legendary"]]);
			}
		});
	}

	public function getPlayerSpecificKey(int $id, string $type, callable $callback) : void{
		$this->database->executeSelectRaw("SELECT " . $type . " FROM playerkeys WHERE playerID = " . $id, [], function(array $rows) use ($type, $id, $callback){
			if(count($rows) == 0){
				$this->database->executeInsert("keys.insert", ["id" => $id]);
				$callback(0);
			}else{
				$callback($rows[0]);
			}
		});
	}

	public function giveKeys(string $uuid, $amount, $type) : void{
		$this->getPlayerID($uuid, function($id) use ($amount, $type){
			$this->database->executeGenericRaw('UPDATE skyblock.playerkeys SET ' . $type . ' = ' . $type . ' + ' . $amount . ' WHERE playerID = ' . $id . ';');
		});
	}

	public function getPlayerID(string $uuid, callable $callback) : void{
		$this->database->executeSelect("player.select", ["uuid" => $uuid], function(array $rows) use ($callback){
			$id = $rows[0]["id"];
			$callback($id);
		});
	}

	public function removeKeys(string $uuid, $amount, $type) : void{
		$this->getPlayerID($uuid, function($id) use ($amount, $type){
			$this->database->executeGenericRaw('UPDATE skyblock.playerkeys SET ' . $type . ' = ' . $type . ' - ' . $amount . ' WHERE playerID = ' . $id . ';');
		});
	}

	public function resetKeys(string $getUniqueId) : void{
		$this->getPlayerID($getUniqueId, function($id){
			$this->database->executeGeneric("keys.delete", ["id" => $id]);
		});
	}

	public function removeOneKey(int $id, string $type){
		$this->database->executeGenericRaw('UPDATE skyblock.playerkeys SET ' . $type . ' = ' . $type . ' - 1 ' . ' WHERE playerID = ' . $id . ';');
	}
}