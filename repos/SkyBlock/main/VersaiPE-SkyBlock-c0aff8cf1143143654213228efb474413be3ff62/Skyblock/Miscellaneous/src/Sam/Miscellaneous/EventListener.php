<?php


namespace Sam\Miscellaneous;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use Sam\Miscellaneous\database\Manager;

class EventListener implements Listener{

	private Manager $database;

	public function __construct($database){
		$this->database = $database;
	}

	public function onJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$uuid = $player->getUniqueId();
		$name = $player->getName();

		$this->database->getPlayerID($uuid, $name , function($data){});
	}
}