<?php
declare(strict_types=1);

namespace Duo\vcosmetics\database;

use pocketmine\player\Player;
use Duo\vcosmetics\Main;

abstract class Provider {

    protected Main $plugin;

    public function __construct(Main $plugin, callable $initialize){
        $this->plugin = $plugin;
        $this->initialize($initialize);
    }

    /**
     * @param callable $onComplete
     */
    abstract public function initialize(callable $onComplete) : void;

    /**
     * @param Player $player
     * @param callable|null $onComplete
     * @return Provider
     */
    abstract public function asyncRegisterPlayerAll(Player $player, callable $onComplete = null) : Provider;

    /**
     * @param Player $player
     * @param callable|null $onComplete
     * @return Provider
     */
    abstract public function asyncUpdatePlayer(Player $player, callable $onComplete = null) : Provider;

    /**
     * @param Player $player
     * @param callable $callback
     * @return Provider
     */
    abstract public function asyncGetPlayer(Player $player, callable $callback) : Provider;
}