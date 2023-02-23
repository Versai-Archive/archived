<?php
declare(strict_types=1);

namespace Versai\Duels\Items;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Food;
use pocketmine\item\ItemIdentifier;

class GoldenHead extends Food{

    private ItemIdentifier $id;

	/**
	 * GoldenHead constructor.
	 * @param ItemIdentifier $id
	 * @param string $name
	 */
	public function __construct(ItemIdentifier $id, string $name = 'Golden Head'){
		parent::__construct($id, $name);
	}

	/**
	 * @return bool
	 */
	public function requiresHunger() : bool{
		return false;
	}

	/**
	 * @return int
	 */
	public function getFoodRestore() : int{
		return 4;
	}

	/**
	 * @return float
	 */
	public function getSaturationRestore() : float{
		return 9.6;
	}

	/**
	 * @return int
	 */
	public function getCooldownTicks(): int{
		return 4;
	}

	public function getAdditionalEffects(): array{
		return [
		    new EffectInstance(VanillaEffects::ABSORPTION(), (10 * 20), 2),
			new EffectInstance(VanillaEffects::REGENERATION(), (5 * 20), 1), // 5 second Regen 2
            new EffectInstance(VanillaEffects::SPEED(), (8 * 20), 0) // 8 second Resistance 3
		];
	}
}