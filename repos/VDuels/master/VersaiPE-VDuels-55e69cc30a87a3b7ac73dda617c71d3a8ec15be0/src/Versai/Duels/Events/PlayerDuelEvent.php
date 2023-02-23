<?php
declare(strict_types=1);

namespace Versai\Duels\Events;

use pocketmine\player\Player;
use Versai\Duels\Match\Task\Heartbeat;


class PlayerDuelEvent extends MatchEvent {

	/** @var Player $heartbeat */
	private Player $player;

	/**
	 * PlayerWinEvent constructor.
	 * @param Player $player
	 * @param Heartbeat $heartbeat
	 */
	public function __construct(Heartbeat $heartbeat, Player $player)
	{
		parent::__construct($heartbeat);
		$this->player = $player;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player
	{
		return $this->player;
	}
}