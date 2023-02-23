<?php
declare(strict_types=1);

namespace Duo\vcosmetics\database;

use Closure;
use Generator;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;
use Throwable;

class MySQLProvider extends Provider implements Queries {

    public function initialize(callable $onComplete): void{
        Await::f2c(function(){
            yield $this->asyncGenericQuery(self::INIT_TABLE);
        }, $onComplete, $this->getOnError());
    }

    public function asyncRegisterPlayerAll(Player $player, callable $onComplete = null): Provider{
        if($onComplete !== null){
            Utils::validateCallableSignature(function(){}, $onComplete);
        }
        Await::f2c(function() use ($player, $onComplete) : Generator{
            yield $this->asyncGenericQuery(self::INSERT_PLAYER, [
                'name' => $player->getName(),
                'xuid' => $this->plugin->playerData->getXUID($player->getName()),
                'cape' => 0,
                'spawnFlight' => 0,
                'followParticle' => 0,
                'hitParticle' => 0,
                'tag' => 0,
                'clanTag' => 0,
                'customTag' => 'None'
            ]);
        }, $onComplete, $this->getOnError());
        return $this;
    }

    public function asyncUpdatePlayer(Player $player, callable $onComplete = null): Provider{
        if($onComplete !== null){
            Utils::validateCallableSignature(function(){}, $onComplete);
        }
        $session = $this->plugin->getSessionManager()->getSession($player);
        Await::f2c(function() use ($player, $session, $onComplete) : Generator{
            yield $this->asyncChange(self::UPDATE_PLAYER, [
                'name' => $player->getName(),
                'cape' => $session->getCape(),
                'spawnFlight' => (int)$session->getSpawnFlight(),
                'followParticle' => $session->getFollowParticle(),
                'hitParticle' => $session->getHitParticle(),
                'tag' => $session->getTag(),
                'clanTag' => $session->getClanTag(),
                'customTag' => $session->getCustomTag()
            ]);
        }, $onComplete, $this->getOnError());
        return $this;
    }

    public function asyncGetPlayer(Player $player, callable $callback) : Provider{
        Utils::validateCallableSignature(function(array $result){}, $callback);
        Await::f2c(function() use ($player, $callback) : Generator{
            $result = yield $this->asyncSelect(self::SELECT_PLAYER, [
                'name' => $player->getName()
            ]);
            $callback($result);
        }, null, $this->getOnError());
        return $this;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncGenericQuery(string $query, array $args = []) : Generator{
        $this->plugin->getDatabase()->executeGeneric($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncSelect(string $query, array $args = []) : Generator{
        $this->plugin->getDatabase()->executeSelect($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncInsert(string $query, array $args = []) : Generator{
        $this->plugin->getDatabase()->executeInsert($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncChange(string $query, array $args = []) : Generator{
        $this->plugin->getDatabase()->executeChange($query, $args, yield, yield Await::REJECT);
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