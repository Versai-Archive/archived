<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/9/2019
 * Time: 7:59 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Commands;

use pocketmine\Player;
use pocketmine\command\Command;

use ARTulloss\Duels\Manager;
use ARTulloss\Duels\Match\DuelManager;
use ARTulloss\Duels\Party\PartyManager;

/**
 * Class SubCommand
 * @package ARTulloss\Duels\Commands
 */
abstract class SubCommand
{
	/** @var PartyCommand|DuelCommand $command */
	protected $command;
	/** @var PartyManager|DuelManager $manager */
	protected $manager;

	/**
	 * SubCommand constructor.
	 * @param Manager $manager
	 * @param Command|null $command
	 */
	public function __construct(Manager $manager, Command $command = null) {
		$this->command = $command;
		$this->manager = $manager;
	}

	/**
	 * @param Player $sender
	 * @param array $args
	 */
	abstract public function execute(Player $sender, array $args): void;

}