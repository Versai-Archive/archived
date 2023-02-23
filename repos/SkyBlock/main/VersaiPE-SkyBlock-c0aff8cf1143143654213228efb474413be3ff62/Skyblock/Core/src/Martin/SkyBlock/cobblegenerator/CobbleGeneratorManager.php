<?php


namespace Martin\SkyBlock\cobblegenerator;


use Martin\SkyBlock\cobblegenerator\block\LavaBlock;
use Martin\SkyBlock\Loader;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use function array_filter;
use function count;
use function is_int;
use function mt_rand;
use function round;

class CobbleGeneratorManager{
	private Loader $loader;

	private ?int $cobbleStoneChance;
	private ?int $coalChance;
	private ?int $ironChance;
	private ?int $goldChance;
	private ?int $diamondChance;
	private ?int $emeraldChance;
	private ?int $netheriteChance;
	private ?int $lapisChance;
	private bool $enabled;

	public function __construct(Loader $loader){
		$this->loader = $loader;
		$config = $loader->getConfig();

		$this->cobbleStoneChance = $config->getNested("cobble-generator.cobblestone", 0);
		$this->coalChance = $config->getNested("cobble-generator.coal", 0);
		$this->ironChance = $config->getNested("cobble-generator.iron", 0);
		$this->goldChance = $config->getNested("cobble-generator.gold", 0);
		$this->diamondChance = $config->getNested("cobble-generator.diamond", 0);
		$this->emeraldChance = $config->getNested("cobble-generator.emerald", 0);
		$this->netheriteChance = $config->getNested("cobble-generator.netherite", 0);
		$this->lapisChance = $config->getNested("cobble-generator.lapis", 0);

		BlockFactory::registerBlock(new LavaBlock($this), true);

		$this->enabled = $this->calculateAll() === 100;
	}

	public function calculateAll() : int{
		$sum = 0;
		foreach([$this->getCobbleStoneChance(), $this->getCoalChance(), $this->getIronChance(), $this->getGoldChance(), $this->getDiamondChance(), $this->getEmeraldChance(), $this->getNetheriteChance()] as $chance){
			if(is_int($chance)){
				$sum += $chance;
			}
		}

		return $sum;
	}

	public function getCobbleStoneChance() : ?int{
		return $this->cobbleStoneChance;
	}

	public function getCoalChance() : ?int{
		return $this->coalChance;
	}

	public function getIronChance() : ?int{
		return $this->ironChance;
	}

	public function getGoldChance() : ?int{
		return $this->goldChance;
	}

	public function getDiamondChance() : ?int{
		return $this->diamondChance;
	}

	public function getEmeraldChance() : ?int{
		return $this->emeraldChance;
	}

	public function getNetheriteChance() : ?int{
		return $this->netheriteChance;
	}

	/**
	 * @return Loader
	 */
	public function getLoader() : Loader{
		return $this->loader;
	}

	private function calculateAverage(int $runningTimes) : void{
		$ores = [];
		$s = microtime(true);
		foreach(range(1, $runningTimes) as $i){
			$ores[] = $this->generateItem();
		}
		echo "Time taken to generate $runningTimes ores: " . (microtime(true) - $s) . "\n";

		$filterCoal = array_filter($ores, static function(int $value){
			return $value === Block::COAL_ORE;
		});
		$filterIron = array_filter($ores, static function(int $value){
			return $value === Block::IRON_ORE;
		});
		$filterCobble = array_filter($ores, static function(int $value){
			return $value === Block::COBBLESTONE;
		});
		$filterGold = array_filter($ores, static function(int $value){
			return $value === Block::GOLD_ORE;
		});
		$filterDiamond = array_filter($ores, static function(int $value){
			return $value === Block::DIAMOND_ORE;
		});
		$filterEmerald = array_filter($ores, static function(int $value){
			return $value === Block::EMERALD_ORE;
		});
		$filterNether = array_filter($ores, static function(int $value){
			return $value === Block::NETHER_BRICK_BLOCK;
		});

		print("Cobble: " . round(((count($filterCobble) / $runningTimes)) * 100, 2) . "%\n");
		print("Coal: " . round(((count($filterCoal) / $runningTimes)) * 100, 2) . "%\n");
		print("Iron: " . round(((count($filterIron) / $runningTimes)) * 100, 2) . "%\n");
		print("Gold: " . round(((count($filterGold) / $runningTimes)) * 100, 2) . "%\n");
		print("Diamond: " . round(((count($filterDiamond) / $runningTimes)) * 100, 2) . "%\n");
		print("Emerald: " . round(((count($filterEmerald) / $runningTimes)) * 100, 2) . "%\n");
		print("Nether: " . round(((count($filterNether) / $runningTimes)) * 100, 2) . "%\n");
	}

	public function generateItem() : int{
		if(!$this->isEnabled()){
			return Block::COBBLESTONE;
		}

		/** @noinspection RandomApiMigrationInspection */
		$number = mt_rand(1, 100); # 40

		$cobble = $this->getCobbleStoneChance(); # 50
		$coal = $cobble + $this->getCoalChance(); # 10
		$iron = $coal + $this->getIronChance(); # 10
		$gold = $iron + $this->getGoldChance(); # 10
		$diamond = $gold + $this->getDiamondChance(); # 10
		$lapis = $diamond + $this->getLapisChance();
		$emerald = $lapis + $this->getEmeraldChance(); # 5
		$netherite = $emerald + $this->getNetheriteChance(); # 5

		if($number <= $cobble){
			return Block::COBBLESTONE;
		}

		if($number <= $coal){
			return Block::COAL_ORE;
		}

		if($number <= $iron){
			return Block::IRON_ORE;
		}

		if($number <= $gold){
			return Block::GOLD_ORE;
		}

		if($number <= $diamond){
			return Block::DIAMOND_ORE;
		}

		if($number <= $lapis){
			return Block::LAPIS_ORE;
		}

		if($number <= $emerald){
			return Block::EMERALD_ORE;
		}

		if($number <= $netherite){
			return Block::NETHER_BRICK_BLOCK;
		}

		return Block::COBBLESTONE;
	}

	public function isEnabled() : bool{
		return $this->enabled;
	}

	public function getLapisChance() : ?int{
		return $this->lapisChance;
	}
}