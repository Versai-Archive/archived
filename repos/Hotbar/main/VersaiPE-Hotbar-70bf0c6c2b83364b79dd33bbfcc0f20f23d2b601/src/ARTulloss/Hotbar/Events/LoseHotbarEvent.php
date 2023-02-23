<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Events;

use ARTulloss\Hotbar\HotbarUser;
use pocketmine\event\Event;

class LoseHotbarEvent extends Event {

    /** @var HotbarUser */
    private HotbarUser $hotbarUser;

    /**
     * LoseHotbarEvent constructor.
     * @param HotbarUser $hotbarUser
     */
    public function __construct(HotbarUser $hotbarUser) {
        $this->hotbarUser = $hotbarUser;
    }

    /**
     * @return HotbarUser
     */
    public function getHotbarUser(): HotbarUser {
        return $this->hotbarUser;
    }
}