<?php
declare(strict_types=1);

namespace Versai\Disguise;

use pocketmine\entity\Skin;
use pocketmine\player\Player;

class DisguisedPlayer
{
	/** @var Player $player */
	private Player $player;
	/** @var Disguise $disguise */
	private Disguise $disguise;
	/** @var Disguise $oldDisguise */
	private Disguise $oldDisguise;
	/** @var Skin $oldSkin */
	private Skin $oldSkin;
	/** @var DisguiseAccessor $accessor */
	private DisguiseAccessor $accessor;

	/**
	 * DisguisedPlayer constructor.
	 * @param Player $player
	 * @param Disguise $disguise
	 * @param Disguise $oldDisguise
	 * @param Skin $oldSkin
	 * @param DisguiseAccessor $accessor
	 */
	public function __construct(Player $player, Disguise $disguise, Disguise $oldDisguise, Skin $oldSkin, DisguiseAccessor $accessor)
	{
		$this->player = $player;
		$this->disguise = $disguise;
		$this->oldDisguise = $oldDisguise;
		$this->oldSkin = $oldSkin;
		$this->accessor = $accessor;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player
	{
		return $this->player;
	}

	/**
	 * @return Disguise
	 */
	public function getDisguise(): Disguise
	{
		return $this->disguise;
	}

	/**
	 * @param Disguise $disguise
	 */
	public function setDisguise(Disguise $disguise): void
	{
		$this->oldDisguise = $this->disguise;
		$this->disguise = $disguise;
	}

	/**
	 * @return Disguise|null
	 */
	public function getOldDisguise(): ?Disguise
	{
		return $this->oldDisguise;
	}

	/**
	 * @return Skin
	 */
	public function getOldSkin(): Skin
	{
		return $this->oldSkin;
	}

	/**
	 * @see DisguiseAccessor::registerDisguisedPlayer()
	 */
	public function register(): void
	{
		$this->accessor->registerDisguisedPlayer($this);
	}

	/**
	 * @see DisguiseAccessor::unregisterDisguisedPlayer()
	 */
	public function unregister(): void
	{
		$this->accessor->unregisterDisguisedPlayer($this);
	}

	/**
	 * @return DisguiseAccessor
	 */
	public function getAccessor(): DisguiseAccessor
	{
		return $this->accessor;
	}

}