<?php
declare(strict_types=1);

namespace Versai\vTempRanks;

use pocketmine\plugin\PluginBase;
use Versai\vTempRanks\commands\TempRankCommand;
use Versai\vTempRanks\database\MySQLProvider;
use Versai\vTempRanks\database\Provider;
use Versai\vTempRanks\libs\poggit\libasynql\DataConnector;
use Versai\vTempRanks\libs\poggit\libasynql\libasynql;

class Main extends PluginBase {

    private DataConnector $dataConnector;
    private Provider $provider;

    public function onEnable(): void{
        $this->saveDefaultConfig();
        $this->initDatabase();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getServer()->getCommandMap()->register("tempranks", new TempRankCommand($this, "temprank", "Give a temporary rank!"));
    }

    public function onDisable(): void{
        if(isset($this->dataConnector)) $this->dataConnector->close();
    }

    public function initDatabase(): void{
        $this->dataConnector = libasynql::create($this, $this->getConfig()->get("database"), [
            "mysql" => "mysql.sql"
        ]);
        $this->provider = new MySQLProvider($this, function(): void{
            $this->getLogger()->info("Database loaded!");
        });
    }

    public function getDataConnector(): DataConnector{
        return $this->dataConnector;
    }

    public function getProvider(): Provider{
        return $this->provider;
    }
}