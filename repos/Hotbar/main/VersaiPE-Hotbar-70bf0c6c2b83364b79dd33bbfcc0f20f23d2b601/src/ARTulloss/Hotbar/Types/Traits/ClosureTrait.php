<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Types\Traits;

use pocketmine\player\Player;
use pocketmine\utils\Utils;
use Closure;

trait ClosureTrait {

    /** @var Closure $closure */
    private Closure $closure;

    /**
     * @param Closure $closure
     */
    public function setClosure(Closure $closure): void {
        $this->closure = $closure;
    }

    /**
     * @return Closure|null
     */
    public function getClosure(): ?Closure {
        return $this->closure;
    }

    /**
     * @param Player $player
     * @param int $slot
     */
    public function executeClosure(Player $player, int $slot): void {
        $closure = $this->closure;
        Utils::validateCallableSignature(function (Player $player, int $slot): void{}, $closure);
        $closure($player, $slot);
    }
}