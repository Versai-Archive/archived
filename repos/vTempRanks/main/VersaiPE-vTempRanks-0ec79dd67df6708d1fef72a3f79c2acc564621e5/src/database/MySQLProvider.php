<?php
declare(strict_types=1);

namespace Versai\vTempRanks\database;

use Closure;
use Generator;
use pocketmine\utils\Utils;
use Throwable;
use Versai\vTempRanks\libs\SOFe\AwaitGenerator\Await;

class MySQLProvider extends Provider implements Queries {


    public function initialize(callable $onComplete): void{
        Await::f2c(function(){
            yield $this->asyncGenericQuery(self::INIT_TABLE);
        }, $onComplete, $this->getOnError());
    }

    public function asyncAddTempRank(string $name, string $rank, int $until, callable $onComplete = null): Provider{
        if($onComplete !== null){
            Utils::validateCallableSignature(function(){}, $onComplete);
        }
        Await::f2c(function() use ($name, $rank, $until, $onComplete) : Generator{
            yield $this->asyncGenericQuery(self::INSERT_PLAYER, [
                'name' => $name,
                'rank' => $rank,
                'until' => $until
            ]);
        }, $onComplete, $this->getOnError());
        return $this;
    }

    public function asyncGetPlayer(string $name, callable $callback) : Provider{
        Utils::validateCallableSignature(function(array $result){}, $callback);
        Await::f2c(function() use ($name, $callback) : Generator{
            $result = yield $this->asyncSelect(self::SELECT_PLAYER, [
                'name' => $name
            ]);
            $callback($result);
        }, null, $this->getOnError());
        return $this;
    }

    public function asyncResetPlayerRank(string $name, string $rank, callable $callback = null): void{
        Await::f2c(function() use ($name, $rank, $callback) : Generator{
            yield $this->asyncChange(self::RESET_RANK, [
                "name" => $name,
                "rank" => $rank
            ]);
        }, $callback, $this->getOnError());
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncGenericQuery(string $query, array $args = []) : Generator{
        $this->dataConnector->executeGeneric($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncSelect(string $query, array $args = []) : Generator{
        $this->dataConnector->executeSelect($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncInsert(string $query, array $args = []) : Generator{
        $this->dataConnector->executeInsert($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncChange(string $query, array $args = []) : Generator{
        $this->dataConnector->executeChange($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @return Closure
     */
    public function getOnError() : Closure{
        return function(Throwable $error) : void{
            $this->plugin->getServer()->getLogger()->logException($error);
        };
    }
}