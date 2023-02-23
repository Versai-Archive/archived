<?php

declare(strict_types=1);

namespace Versai\Disguise;

use pocketmine\plugin\PluginBase;
use Versai\Disguise\Events\Listener;
use Versai\Disguise\Command\DisguiseCommand;

class Main extends PluginBase
{
	/** @var DisguisedPlayerFactory $disguisedPlayerFactory */
	private DisguisedPlayerFactory $disguisedPlayerFactory;
	/** @var NameAccessor $nameAccessor */
	private NameAccessor $nameAccessor;

	public function onEnable(): void
	{
		$disguiseAccessor = new DisguiseAccessor();
		$this->nameAccessor = new NameAccessor();
		$this->disguisedPlayerFactory = new DisguisedPlayerFactory($disguiseAccessor);
		$server = $this->getServer();
		$server->getCommandMap()->register('disguise', new DisguiseCommand($this, $disguiseAccessor,'disguise', 'Become a random player!', '/disguise <me> <off> <list>'));
		$server->getPluginManager()->registerEvents(new Listener($disguiseAccessor), $this);
	}

	/**
	 * @return DisguisedPlayerFactory
	 */
	public function getDisguisedPlayerFactory(): DisguisedPlayerFactory
	{
		return $this->disguisedPlayerFactory;
	}

	/**
	 * @return NameAccessor
	 */
	public function getNameAccessor(): NameAccessor
	{
		return $this->nameAccessor;
	}
}
