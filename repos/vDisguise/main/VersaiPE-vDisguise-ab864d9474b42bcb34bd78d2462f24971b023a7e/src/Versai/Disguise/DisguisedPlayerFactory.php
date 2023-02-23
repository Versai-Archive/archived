<?php
declare(strict_types=1);

namespace Versai\Disguise;

use pocketmine\entity\Skin;
use pocketmine\player\Player;


class DisguisedPlayerFactory
{
	/** @var DisguiseAccessor $accessor */
	private DisguiseAccessor $accessor;

	/**
	 * DisguisedPlayerFactory constructor.
	 * @param DisguiseAccessor $accessor
	 */
	public function __construct(DisguiseAccessor $accessor)
	{
		$this->accessor = $accessor;
	}

	/**
	 * @param Player $player
	 * @param Disguise $disguise
	 * @param Disguise $oldDisguise
	 * @param Skin $oldSkin
	 * @return DisguisedPlayer
	 */
	public final function new(Player $player, Disguise $disguise, Disguise $oldDisguise, Skin $oldSkin): DisguisedPlayer
	{
		return new DisguisedPlayer($player, $disguise, $oldDisguise, $oldSkin, $this->accessor);
	}
}