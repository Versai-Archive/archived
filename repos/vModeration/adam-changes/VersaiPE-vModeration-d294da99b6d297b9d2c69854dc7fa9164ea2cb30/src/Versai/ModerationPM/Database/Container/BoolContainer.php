<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Database\Container;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class BoolContainer{

    protected Plugin $plugin;
    /** @var bool[] $cache */
    protected array $cache;

    /**
     * Cache constructor.
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     */
    public function action(Player $player): void{
        $this->cache[$player->getName()] = true;
    }

    /**
     * @param Player $player
     */
    public function reverseAction(Player $player): void{
        unset($this->cache[$player->getName()]);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function checkState(Player $player): bool{
        return isset($this->cache[$player->getName()]);
    }
}
