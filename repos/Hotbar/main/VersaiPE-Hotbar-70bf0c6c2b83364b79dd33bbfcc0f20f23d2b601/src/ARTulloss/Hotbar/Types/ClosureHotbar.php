<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Types;

use ARTulloss\Hotbar\Types\Traits\ClosureTrait;
use pocketmine\player\Player;

class ClosureHotbar extends Hotbar {

    use ClosureTrait;
    /**
     * @param Player $player
     * @param int $slot
     */
    public function execute(Player $player, int $slot): void {
        $this->executeClosure($player, $slot);
    }
}