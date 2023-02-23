<?php
declare(strict_types=1);

namespace Versai\Disguise;

class DisguiseAccessor
{
	/** @var DisguisedPlayer[] $disguisedPlayers */
	private array $disguisedPlayers;

	/**
	 * @return DisguisedPlayer[]
	 */
	public function getDisguisedPlayers(): array
	{
		return (array) $this->disguisedPlayers;
	}

	/**
	 * @param DisguisedPlayer $disguisedPlayer
	 */
	public function registerDisguisedPlayer(DisguisedPlayer $disguisedPlayer): void
	{
		$name = $disguisedPlayer->getPlayer()->getName();
		$this->disguisedPlayers[$name] = $disguisedPlayer;
	}

	/**
	 * @param DisguisedPlayer $disguisedPlayer
	 */
	public function unregisterDisguisedPlayer(DisguisedPlayer $disguisedPlayer): void
	{
		$this->unregisterDisguisedPlayerByName($disguisedPlayer->getPlayer()->getName());
	}

	/**
	 * @param string $name
	 */
	public function unregisterDisguisedPlayerByName(string $name): void
	{
		unset($this->disguisedPlayers[$name]);
	}
}