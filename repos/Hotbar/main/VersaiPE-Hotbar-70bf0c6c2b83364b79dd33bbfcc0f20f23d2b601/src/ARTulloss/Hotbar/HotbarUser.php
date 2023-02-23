<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar;

use ARTulloss\Hotbar\Types\HotbarInterface;
use pocketmine\player\Player;

class HotbarUser {

    /** @var Player $player */
    private Player $player;
    /** @var HotbarInterface $hotbar */
    private HotbarInterface $hotbar;
    /** @var int $lastUsage */
    private int $lastUsage = 0;

    /**
     * HotbarUser constructor.
     * @param Player $player
     * @param HotbarInterface $hotbar
     */
    public function __construct(Player $player, HotbarInterface $hotbar) {
        $this->setPlayer($player);
        $this->setHotbar($hotbar);
    }

    /**
     * @param Player $player
     */
    private function setPlayer(Player $player): void {
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player {
        return $this->player;
    }

    /**
     * @param HotbarInterface $hotbar
     */
    public function setHotbar(HotbarInterface $hotbar): void {
        $hotbar->send($this->player);
        $this->hotbar = $hotbar;
    }

    /**
     * @return HotbarInterface
     */
    public function getHotbar(): HotbarInterface {
        return $this->hotbar;
    }

    public function updateLastUsage(): void {
        $this->lastUsage = $this->player->getServer()->getTick();
    }

    /**
     * @return int
     */
    public function getLastUsage(): int {
        return $this->lastUsage;
    }
}