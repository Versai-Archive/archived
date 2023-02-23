<?php


namespace Martin\SkyBlock\cobblegenerator\block;


use Martin\SkyBlock\cobblegenerator\CobbleGeneratorManager;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Lava;
use pocketmine\block\Water;

class LavaBlock extends Lava{
	private CobbleGeneratorManager $cobbleGeneratorManager;

	public function __construct(CobbleGeneratorManager $cobbleGeneratorManager){
		$this->cobbleGeneratorManager = $cobbleGeneratorManager;
		parent::__construct();
	}

	protected function checkForHarden() : void{
		$colliding = null;
		for($side = 1; $side <= 5; ++$side){ //don't check downwards side
			$blockSide = $this->getSide($side);
			if($blockSide instanceof Water){
				$colliding = $blockSide;
				break;
			}
		}

		if($colliding !== null){
			if($this->getDamage() === 0){
				$this->liquidCollide($colliding, BlockFactory::get(Block::OBSIDIAN));
			}elseif($this->getDamage() <= 4){
				$blockId = $this->getCobbleGeneratorManager()->generateItem();
				$this->liquidCollide($colliding, BlockFactory::get($blockId));
			}
		}
	}


	public function getCobbleGeneratorManager() : CobbleGeneratorManager{
		return $this->cobbleGeneratorManager;
	}
}