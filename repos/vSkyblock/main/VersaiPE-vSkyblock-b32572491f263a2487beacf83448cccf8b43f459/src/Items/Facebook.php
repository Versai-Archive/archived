<?php

declare(strict_types=1);

namespace Skyblock\Items;

use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

/**
 * Class Facebook should only be used as a debugging and testing class, for custom items.
 * @package Skyblock\Items
 */
class Facebook extends Item implements ItemComponents {
	use ItemComponentsTrait;

	public function __construct(ItemIdentifier $identifier, string $name = "facebook") {
		parent::__construct($identifier, $name);
		$this->initComponent('facebook', 64);
		$this->addProperty('creative_group', 'Items');
		$this->addProperty('creative_category', 4);
		$this->setupRenderOffsets(512, 512);
	}

}