<?php


namespace Martin\SkyBlock\cooldown\task;


use Martin\SkyBlock\cooldown\CooldownManager;
use pocketmine\scheduler\Task;
use function time;

class CooldownTask extends Task{
	private CooldownManager $cooldownManager;

	public function __construct(CooldownManager $cooldownManager){
		$this->cooldownManager = $cooldownManager;
	}

	public function onRun(int $currentTick) : void{
		foreach($this->cooldownManager->getCooldowns() as $playerName => $cooldown){
			if($cooldown->getTime() >= time()){
				$this->cooldownManager->removePlayer($playerName, $cooldown->getType());
			}
		}
	}
}