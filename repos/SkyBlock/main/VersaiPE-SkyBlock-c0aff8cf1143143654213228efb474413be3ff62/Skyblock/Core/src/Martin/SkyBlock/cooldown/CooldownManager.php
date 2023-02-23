<?php


namespace Martin\SkyBlock\cooldown;


use Martin\SkyBlock\constants\CooldownList;
use Martin\SkyBlock\cooldown\task\CooldownTask;
use Martin\SkyBlock\Loader;
use pocketmine\Player;

class CooldownManager{
	private Loader $loader;

	/** @var Cooldown[] */
	private array $cooldowns = [];

	public function __construct(Loader $loader){
		$this->loader = $loader;
		$this->getLoader()->getScheduler()->scheduleDelayedRepeatingTask(new CooldownTask($this), 1, 20);
	}

	public function getLoader() : Loader{
		return $this->loader;
	}

	public function addPlayer(Player $player, int $type) : void{
		if(!in_array($type, $this->getTypes(), true)){
			return;
		}

		$this->cooldowns[$player->getLowerCaseName()] = new Cooldown($type);
	}

	public function getTypes() : array{
		return [CooldownList::ISLAND_CREATION, CooldownList::ISLAND_DELETION];
	}

	public function inCooldown(Player $player, int $type) : bool{
		foreach($this->cooldowns as $playerName => $cooldown){
			if($playerName !== $player->getLowerCaseName()){
				continue;
			}

			if($cooldown->getType() === $type){
				return true;
			}
		}

		return false;
	}

	public function removePlayer($player, int $type) : void{
		if($player instanceof Player){
			$player = $player->getLowerCaseName();
		}else{
			$player = strtolower($player);
		}
		foreach($this->cooldowns as $playerName => $cooldown){
			if($player === $playerName && $cooldown->getType() === $type){
				unset($this->cooldowns[$playerName]);
			}
		}
	}

	/**
	 * @return Cooldown[]
	 */
	public function getCooldowns() : array{
		return $this->cooldowns;
	}
}