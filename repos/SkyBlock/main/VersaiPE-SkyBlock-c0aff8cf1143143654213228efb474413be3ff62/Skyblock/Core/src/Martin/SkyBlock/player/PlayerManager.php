<?php


namespace Martin\SkyBlock\player;


use Martin\SkyBlock\Loader;
use pocketmine\Player;
use function array_search;

class PlayerManager{
	/** @var SkyBlockPlayer[] */
	public array $sessions = [];

	private Loader $loader;

	public function __construct(Loader $loader){
		$this->loader = $loader;
	}

	public function addPlayer(Player $player) : void{
		if(!$this->hasSession($player)){
			$this->sessions[] = new SkyBlockPlayer($player, $this->getLoader());
		}
	}

	public function hasSession(Player $player) : bool{
		return $this->getSession($player) !== null;
	}

	public function getSession(Player $player) : ?SkyBlockPlayer{
		foreach($this->sessions as $session){
			if($session->getPlayer() === $player){
				return $session;
			}
		}

		return null;
	}

	public function getLoader() : Loader{
		return $this->loader;
	}

	public function removePlayer(Player $player) : void{
		if($session = $this->getSession($player)){
			unset($this->sessions[array_search($session, $this->sessions, true)]);
		}
	}
}