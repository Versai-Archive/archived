<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Items;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Food;

/**
 * Class GoldenHead
 * @package ARTulloss\Duels\Items
 */
class GoldenHead extends Food
{

	# TODO ENABLE FOR ALPHA 4
	# At the moment you can't add item variants :P

	/**
	 * GoldenHead constructor.
	 * @param int $id
	 * @param int $meta
	 * @param string $name
	 */
	public function __construct(int $id, int $meta = 0, string $name = 'Golden Head')
	{
		parent::__construct($id, $meta, $name);
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
	public function getMeta(): int
	{
		return $this->meta;
	}

	/**
	 * @return int
	 */
	public function getCooldownTicks(): int
	{
		return 4;
	}

	public function getAdditionalEffects(): array
	{
		return [
			new EffectInstance(Effect::getEffect(Effect::REGENERATION), 100, 1),
		];
	}
}