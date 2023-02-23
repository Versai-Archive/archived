<?php

namespace Sam\VCrates\task;

use pocketmine\item\Item;
use pocketmine\level\sound\FizzSound;
use pocketmine\level\sound\PopSound;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use Sam\VCrates\Main;
use Sam\VCrates\tile\VCrateTile;

class SpinningTask extends Task{

	/** @var Item[] */
	private array $items;
	private Vector3 $pos;
	private int $amount;
	private VCrateTile $chestPos;
	private int $entityId;


	public function __construct($items, $pos, $amount, $chestPos){
		$this->items = $items;
		$this->pos = $pos;
		$this->amount = $amount;
		$this->chestPos = $chestPos;

	}

	public function onRun(int $currentTick){
		if($this->amount == 1){
			$this->entityId = $this->chestPos->getLevel()->dropItem($this->pos, $this->items[0], new Vector3(0, 0.5, 0), 0)->getId();
			Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClearEntity($this->entityId, $this->chestPos->getLevel()), 80);
			$this->chestPos->getLevel()->addSound(new FizzSound($this->pos));
			$this->chestPos->onWin($this->items[0]);

			Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());

		}else{
			$this->entityId = $this->chestPos->getLevel()->dropItem($this->pos, $this->items[0], new Vector3(0, 0.5, 0), 0)->getId();
			$this->chestPos->getLevel()->addSound(new PopSound($this->pos));
			array_shift($this->items);
			Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClearEntity($this->entityId, $this->chestPos->getLevel()), 30);
			$this->amount--;
		}
	}
}