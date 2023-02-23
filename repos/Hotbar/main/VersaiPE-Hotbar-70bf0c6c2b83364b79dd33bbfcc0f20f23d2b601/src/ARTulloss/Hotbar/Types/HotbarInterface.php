<?php
declare(strict_types=1);

namespace ARTulloss\Hotbar\Types;

use pocketmine\player\Player;
use pocketmine\item\Item;

interface HotbarInterface {

    /**
     * Send the hotbar to player
     * @param Player $player
     */
    public function send(Player $player): void;

    /**
     * Set the hotbars items
     * @param array $items
     */
    public function setItems(array $items): void;

    /**
     * Get the items in the hotbar
     * @return Item[]
     */
    public function getItems(): array;

    /**
     * @param Player $player
     * @param int $slot
     */
    public function execute(Player $player, int $slot): void;

    /**
     * @return string
     */
    public function getName(): string;
}