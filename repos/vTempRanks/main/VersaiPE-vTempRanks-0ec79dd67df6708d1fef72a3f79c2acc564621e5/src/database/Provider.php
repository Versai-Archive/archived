<?php
declare(strict_types=1);

namespace Versai\vTempRanks\database;

use Versai\vTempRanks\libs\poggit\libasynql\DataConnector;
use Versai\vTempRanks\Main;

abstract class Provider
{

    protected Main $plugin;
    protected DataConnector $dataConnector;

    public function __construct(Main $plugin, callable $onComplete)
    {
        $this->plugin = $plugin;
        $this->dataConnector = $plugin->getDataConnector();

        $this->initialize($onComplete);
    }

    /**
     * @param callable $onComplete
     */
    abstract public function initialize(callable $onComplete) : void;

    /**
     * @param string $name
     * @param string $rank
     * @param int $until
     * @param callable|null $onComplete
     * @return Provider
     */
    abstract public function asyncAddTempRank(string $name, string $rank, int $until, callable $onComplete = null) : Provider;

    /**
     * @param string $name
     * @param callable $callback
     * @return Provider
     */
    abstract public function asyncGetPlayer(string $name, callable $callback) : Provider;

    /**
     * @param string $name
     * @param string $rank
     * @param callable|null $callback
     * @return void
     */
    abstract public function asyncResetPlayerRank(string $name, string $rank, callable $callback = null) : void;
}