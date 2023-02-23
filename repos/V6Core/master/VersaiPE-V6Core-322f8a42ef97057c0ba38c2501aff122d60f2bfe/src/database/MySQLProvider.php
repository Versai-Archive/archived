<?php
declare(strict_types=1);

namespace Versai\V6\database;

use _b30023768c12784bd2feSOFe\AwaitGenerator\Await;
use Closure;
use Throwable;
use Generator;

class MySQLProvider extends Provider implements Queries {

    public function initialize(callable $onComplete): void{
        Await::f2c(function(){
            $queries = []; //Table Creation Queries
            foreach($queries as $query){
                yield $this->asyncGenericQuery($query);
            }
        }, $onComplete, $this->getOnError());
    }


    // Example Usage

    /*
    public function asyncGetData(callable $callback): void {
        Utils::validateCallableSignature(function(array $result) : void{}, $callback);
        Await::f2c(function() use ($callback) : Generator {
            $result = yield $this->asyncSelect(self::SELECT_DATA_QUERY_HERE);
            $callback($result);
        }, null, $this->getOnError());
    }
    */

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncGenericQuery(string $query, array $args = []) : Generator{
        $this->loader->databaseManager->getDatabase()->executeGeneric($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncSelect(string $query, array $args = []) : Generator{
        $this->loader->databaseManager->getDatabase()->executeSelect($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncInsert(string $query, array $args = []) : Generator{
        $this->loader->databaseManager->getDatabase()->executeInsert($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncChange(string $query, array $args = []) : Generator{
        $this->loader->databaseManager->getDatabase()->executeChange($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @return Closure
     */
    public function getOnError() : Closure{
        return function(Throwable $error) : void{
            $this->loader->getServer()->getLogger()->logException($error);
        };
    }
}