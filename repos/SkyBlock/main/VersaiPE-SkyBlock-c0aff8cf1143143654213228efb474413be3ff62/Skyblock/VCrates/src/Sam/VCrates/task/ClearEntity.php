<?php


namespace Sam\VCrates\task;


use pocketmine\level\Level;
use pocketmine\scheduler\Task;

class ClearEntity extends Task{

	private int $id;
	private Level $level;

	public function __construct(int $id, Level $level){
		$this->id = $id;
		$this->level = $level;
	}

	public function onRun(int $currentTick){
		$this->level->getEntity($this->id)->flagForDespawn();
	}
}