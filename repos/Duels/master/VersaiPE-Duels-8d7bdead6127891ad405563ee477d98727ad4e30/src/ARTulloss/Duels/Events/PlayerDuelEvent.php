<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/19/2019
 * Time: 3:35 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Events;

use ARTulloss\Duels\Match\Task\Heartbeat;
use pocketmine\Player;

/**
 * Class PlayerWinEvent
 * @package ARTulloss\Duels\Events
 */
class PlayerDuelEvent extends MatchEvent
{
	/** @var Player $heartbeat */
	private $player;

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