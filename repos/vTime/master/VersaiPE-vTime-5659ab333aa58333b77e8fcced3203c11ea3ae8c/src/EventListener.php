<?php
declare(strict_types=1);

namespace Versai\vTime;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use Versai\vTime\data\DatabaseContext;

class EventListener implements Listener{

	private Main $plugin;
	private DatabaseContext $database;

	/**
	 * EventListener constructor.
	 *
	 * @param Main            $plugin
	 * @param DatabaseContext $database
	 */
	public function __construct(Main $plugin, DatabaseContext $database){
		$this->plugin = $plugin;
		$this->database = $database;
	}

	public function onJoin(PlayerJoinEvent $event){
		$username = $event->getPlayer()->getName();
		$this->database->onJoin($username);
	}

	public function onQuit(PlayerQuitEvent $event){
		$username = $event->getPlayer()->getName();
		$this->database->onQuit($username);
	}

}