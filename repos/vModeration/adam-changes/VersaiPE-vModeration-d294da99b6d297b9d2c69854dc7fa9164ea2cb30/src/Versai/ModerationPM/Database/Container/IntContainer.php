<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Database\Container;

use pocketmine\player\Player;

class IntContainer{

    /** @var int[] $cache */
    protected array $cache;

    /**
     * @param Player $player
     * @param int $value
     */
    public function action(Player $player, int $value): void{
        $this->cache[$player->getName()] = $value;
    }

    /**
     * @param Player $player
     */
    public function reverseAction(Player $player): void{
        unset($this->cache[$player->getName()]);
    }

    /**
     * @param Player $player
     * @return int|null
     */
    public function checkState(Player $player): ?int{
        return $this->checkStateByName($player->getName());
    }

    /**
     * @param string $name
     * @return int|null
     */
    public function checkStateByName(string $name): ?int{
        return $this->cache[$name] ?? null;
    }
}
