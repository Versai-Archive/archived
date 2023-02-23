<?php
declare(strict_types=1);

namespace Versai\Duels\Commands;

use pocketmine\command\Command;
use pocketmine\player\Player;
use Versai\Duels\Manager;

abstract class SubCommand {

	/** @var Command|null $command */
	protected ?Command $command;
	/** @var Manager $manager */
	protected Manager $manager;

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