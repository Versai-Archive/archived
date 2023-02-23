<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Types\Traits;

use ARTulloss\Hotbar\Main;
use pocketmine\Server;

trait CommandTrait {

    /** @var string[] $commands */
    private array $commands;

    /**
     * @param int $slot
     * @param string[] $commands
     */
    public function setSlotCommands(int $slot, array $commands): void {
        if($slot < 1 || $slot > 9) {
            Main::getInstance()->getLogger()->error(self::INVALID_SLOT);
        }
        $this->commands[$slot] = $commands;
    }

    /**
     * @return array
     */
    public function getCommands(): array {
        return $this->commands;
    }

    /**
     * @param int $slot
     * @return array|null
     */
    public function getSlotCommands(int $slot): ?array {
        $slot++; // Adjust the slot
        if(isset($this->commands[$slot])) {
            return $this->commands[$slot];
        }
        return null;
    }

    /**
     * @param int $slot
     * @return bool
     */
    public function hasCommands(int $slot): bool {
        return isset($this->commands[$slot]);
    }
}