<?php
declare(strict_types=1);

namespace Versai\V6\database;

use pocketmine\utils\Config;
use Versai\V6\libs\poggit\libasynql\DataConnector;
use Versai\V6\libs\poggit\libasynql\libasynql;
use Versai\V6\Loader;

class DatabaseManager {

    private DataConnector $dataConnector;
    private Provider $database;

    public function __construct(private Loader $loader) {
        $this->dataConnector = libasynql::create($this->loader, (new Config($this->loader->getDataFolder() . "database.yml"))->get("database"), [
            "mysql" => "mysql_stmts.sql"
        ]);
        $this->database = new MySQLProvider($this->loader, function(): void {
            $this->loader->getLogger()->info("Database loaded!");
        });
    }

    public function getDatabase(): DataConnector {
        return $this->dataConnector;
    }

    public function getProvider(): Provider {
        return $this->database;
    }
}