<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Accessor;

use ARTulloss\Hotbar\Main;
use ARTulloss\Hotbar\Types\HotbarInterface;
use pocketmine\world\World;

class HotbarLevels {

    /** @var Main $main */
    private Main $main;
    /** @var HotbarInterface[] $levelHotbars */
    private array $levelHotbars;

    /**
     * HotbarLevels constructor.
     * @param Main $main
     */
    public function __construct(Main $main) {
        $this->main = $main;
    }
    /**
     * @param World $level
     * @param HotbarInterface $hotbar
     */
    public function bindLevelToHotbar(World $level, HotbarInterface $hotbar): void{
        $this->levelHotbars[$level->getDisplayName()] = $hotbar;
    }
    /**
     * @param World $level
     * @return bool
     */
    public function unbindLevelToHotbar(World $level): bool{
        $levelName = $level->getDisplayName();
        $return = isset($this->levelHotbars[$levelName]);
        unset($this->levelHotbars[$levelName]);
        return $return;
    }
    /**
     * @param World $level
     * @return HotbarInterface|null
     */
    public function getHotbarForLevel(World $level): ?HotbarInterface{
        $levelName = $level->getDisplayName();
        if(isset($this->levelHotbars[$levelName])) {
            return $this->levelHotbars[$levelName];
        }
        return null;
    }
    /**
     * @return HotbarInterface[]|null
     */
    public function getAll(): ?array{
        return $this->levelHotbars;
    }
}