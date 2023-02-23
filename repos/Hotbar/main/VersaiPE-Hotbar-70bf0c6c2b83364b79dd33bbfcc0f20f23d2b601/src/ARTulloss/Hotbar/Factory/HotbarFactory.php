<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Factory;

use ARTulloss\Hotbar\Types\ClosureHotbar;
use ARTulloss\Hotbar\Types\CommandHotbar;
use ARTulloss\Hotbar\Types\Hotbar;
use TypeError;
use function strtolower;

class HotbarFactory {

    /**
     * @param string $type
     * @param string $name
     * @param array $items
     * @return Hotbar
     */
    static public function make(string $type, string $name, array $items): Hotbar {
        switch (strtolower($type)) {
            case 'command':
                return new CommandHotbar($name, $items);
            case 'closure':
                return new ClosureHotbar($name, $items);
            default:
                throw new TypeError("Unknown hotbar type in Hotbar named $name");
        }
    }
}