<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/11/2019
 * Time: 3:41 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands\Sub;

use pocketmine\command\Command;
use pocketmine\Player;

use ARTulloss\Duels\Commands\SubCommand;
use ARTulloss\Duels\Manager;

/**
 * Trait BackTrait
 * @package ARTulloss\Duels\Commands\Sub
 */
abstract class BackSubCommand extends SubCommand
{
	/**
	 * BackSubCommand constructor.
	 * @param Manager $manager
	 * @param bool $back
	 * @param null|Command $command
	 */
	public function __construct(Manager $manager, bool $back = false, ?Command $command = null)
	{
		parent::__construct($manager, $command);
		$this->back = $back;
	}

	/** @var bool $back */
	private $back = false;

	/**
	 * @param bool $hasBack
	 */
	public function setBack(bool $hasBack): void
	{
		$this->back = $hasBack;
	}

	/**
	 * @return bool
	 */
	public function hasBack(): bool
	{
		return $this->back;
	}

	/**
	 * @param Player $player
	 */
	public function goBack(Player $player): void
	{
		$player->getServer()->dispatchCommand($player, 'party');
	}

}