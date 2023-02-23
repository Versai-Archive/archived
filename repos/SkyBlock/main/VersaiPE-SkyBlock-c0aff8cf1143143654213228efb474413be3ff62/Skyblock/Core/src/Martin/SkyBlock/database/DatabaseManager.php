<?php


namespace Martin\SkyBlock\database;


use Martin\SkyBlock\constants\Queries;
use Martin\SkyBlock\Loader;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class DatabaseManager{
	private DataConnector $connection;

	public function __construct(Loader $loader){
		$this->connection = libasynql::create($loader, $loader->getConfig()->get("database"), [
			"sqlite" => "map.sql",
			"mysql" => "map.sql"
		]);

		$this->initTables();

		$this->getConnection()->executeSelect(Queries::GET_PLAYER_ALL, [], function(array $rows){
			foreach($rows as $row){
				var_dump($row);
			}
		});
	}

	public function initTables() : void{
		$this->getConnection()->executeGeneric(Queries::INIT_PLAYERS);
		$this->getConnection()->executeGeneric(Queries::INIT_ISLANDS);
	}

	public function getConnection() : DataConnector{
		return $this->connection;
	}
}