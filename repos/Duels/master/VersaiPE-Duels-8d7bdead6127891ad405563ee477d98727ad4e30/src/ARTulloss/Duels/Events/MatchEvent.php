<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/19/2019
 * Time: 2:51 PM
 */
declare(strict_types=1);


namespace ARTulloss\Duels\Events;

use pocketmine\event\Event;

use ARTulloss\Duels\Match\Task\Heartbeat;

/**
 * Class MatchEndEvent
 * @package ARTulloss\Duels\Events
 */
abstract class MatchEvent extends Event
{
	/** @var Heartbeat $heartbeat */
	private $heartbeat;
	/**
	 * MatchEvent constructor.
	 * @param Heartbeat $heartbeat
	 */
	public function __construct(Heartbeat $heartbeat)
	{
		$this->heartbeat = $heartbeat;
	}
	/**
	 * @return Heartbeat
	 */
	public function getHeartbeat(): Heartbeat
	{
		return $this->heartbeat;
	}

}