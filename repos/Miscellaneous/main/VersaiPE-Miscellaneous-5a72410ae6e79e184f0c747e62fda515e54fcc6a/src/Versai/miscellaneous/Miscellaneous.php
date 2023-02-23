<?php
declare(strict_types=1);

namespace Versai\miscellaneous;

use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\query\QueryInfo;
use pocketmine\plugin\PluginBase;

use Versai\miscellaneous\commands\Ping;
use Versai\miscellaneous\commands\Gamemode;
use Versai\miscellaneous\commands\Popcorn;
use Versai\miscellaneous\events\Listener;
use Versai\miscellaneous\tasks\MessageTask;

class Miscellaneous extends PluginBase {

    public array $config;

    public function onEnable(): void {
        $this->saveDefaultConfig();
		$server = $this->getServer();
		$server->getCommandMap()->registerAll('misc', [
			new Ping('ping', 'Check your ping', '/ping <player>', ['ms']),
			new Gamemode('gm', Constants::GAMEMODE_DESCRIPTION, '/gm <c> <s> <spec>'),
            new Popcorn('popcorn', 'Popcorn players', '/pc <player> <radius>', ['pc']),
		]);
		$this->getServer()->getPluginManager()->registerEvents(($listener = new Listener($this)), $this);
        $this->config = $this->getConfig()->getAll();
		$listener->onQuery(new QueryRegenerateEvent(new QueryInfo($server)));
        $this->getScheduler()->scheduleRepeatingTask(new MessageTask($this->getServer(), $this->config), 20 * $this->config["every"]);
	}
}
