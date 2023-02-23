<?php


namespace Sam\VCrates\task;


use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\scheduler\Task;

class TextTask extends Task{

	private Level $level;
	private FloatingTextParticle $class;

	public function __construct($level, $class){
		$this->level = $level;
		$this->class = $class;
	}

	public function onRun(int $currentTick){
		$this->class->setInvisible();
		$this->level->addParticle($this->class, $this->level->getPlayers());
	}
}