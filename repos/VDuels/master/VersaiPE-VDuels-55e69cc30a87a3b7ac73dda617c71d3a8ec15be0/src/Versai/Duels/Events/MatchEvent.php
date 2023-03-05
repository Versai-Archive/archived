<?php
declare(strict_types=1);


namespace Versai\Duels\Events;

use pocketmine\event\Event;
use Versai\Duels\Match\Task\Heartbeat;


abstract class MatchEvent extends Event {

	/** @var Heartbeat $heartbeat */
	private Heartbeat $heartbeat;
	/**
	 * MatchEvent constructor.
	 * @param Heartbeat $heartbeat
	 */
	public function __construct(Heartbeat $heartbeat) {
		$this->heartbeat = $heartbeat;
	}

	/**
	 * @return Heartbeat
	 */
	public function getHeartbeat(): Heartbeat {
		return $this->heartbeat;
	}

}