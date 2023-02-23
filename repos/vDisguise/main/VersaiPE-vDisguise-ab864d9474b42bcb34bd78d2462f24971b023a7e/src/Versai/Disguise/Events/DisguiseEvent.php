<?php
declare(strict_types=1);

namespace Versai\Disguise\Events;

use pocketmine\event\Event;
use Versai\Disguise\DisguisedPlayer;

/**
 * Class DisguiseEvent
 * @package ARTulloss\Disguise\Events
 */
abstract class DisguiseEvent extends Event
{
    /**
     * @param DisguisedPlayer $disguisedPlayer
     */
	public function __construct(private DisguisedPlayer $disguisedPlayer){}

	/**
	 * @return DisguisedPlayer
	 */
	public function getDisguisedPlayer(): DisguisedPlayer
	{
		return $this->disguisedPlayer;
	}
}