<?php


namespace Martin\SkyBlock\island;


use Martin\SkyBlock\Loader;

class IslandManager{
	private Loader $loader;
	/** @var Island[] */
	private array $loadedIslands = [];

	public function __construct(Loader $loader){
		$this->loader = $loader;
	}

	public function createIsland(string $player) : Island{
		return new Island($this->getLoader());
	}

	public function getLoader() : Loader{
		return $this->loader;
	}

	/**
	 * @return Island[]
	 */
	public function getIslands(string $player) : array{
		$islands = [];

		return $islands;
	}

	public function removeIsland(Island $island) : void{

	}

	public function canAddIsland(string $player) : bool{

		return true;
	}

	public function transferIsland(Island $island, string $player) : void{

	}
}