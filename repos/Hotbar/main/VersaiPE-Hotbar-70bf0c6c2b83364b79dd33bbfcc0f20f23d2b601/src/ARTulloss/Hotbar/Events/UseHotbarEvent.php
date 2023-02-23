<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Events;

use ARTulloss\Hotbar\HotbarUser;
use pocketmine\event\Event;

class UseHotbarEvent extends Event {

    /** @var HotbarUser $hotbarUser */
    private HotbarUser $hotbarUser;
    /** @var int $slot */
    private int $slot;

    /**
     * UseHotbarEvent constructor.
     * @param HotbarUser $hotbarUser
     * @param int $slot
     */
    public function __construct(HotbarUser $hotbarUser, int $slot) {
        $this->hotbarUser = $hotbarUser;
        $this->slot = $slot;
    }

    /**
     * @return HotbarUser
     */
    public function getHotbarUser(): HotbarUser {
        return $this->hotbarUser;
    }

    /**
     * @return int
     */
    public function getSlot(): int {
        return $this->slot;
    }
}
