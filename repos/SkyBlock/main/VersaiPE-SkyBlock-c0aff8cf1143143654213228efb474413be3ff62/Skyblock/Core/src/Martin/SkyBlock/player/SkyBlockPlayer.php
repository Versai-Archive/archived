<?php


namespace Martin\SkyBlock\player;


use Martin\SkyBlock\constants\JobList;
use Martin\SkyBlock\constants\Queries;
use Martin\SkyBlock\constants\RankList;
use Martin\SkyBlock\Loader;
use pocketmine\Player;

class SkyBlockPlayer{
	private Player $player;
	private Loader $loader;

	private int $rank = RankList::UNRANKED;
	private int $job = JobList::UNEMPLOYED;

	public function __construct(Player $player, Loader $loader){
		$this->player = $player;
		$this->loader = $loader;

		foreach(range(1, 10) as $i){
			var_dump($this->hasData());
		}

		if(!$this->hasData()){
			$this->createData();
		}

		$this->initalizePlayerFromData();
	}

	public function hasData() : bool{
		$returningValue = false;

		$this->getLoader()->getDatabaseManager()->getConnection()->executeSelectRaw(Queries::GET_PLAYER_BY_USERNAME, ["name" => $this->getPlayer()->getLowerCaseName()], function(array $rows) use (&$returningValue) : void{
			if(empty($rows)){
				$returningValue = true;
			}
		});


		return $returningValue;
	}

	public function getLoader() : Loader{
		return $this->loader;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function createData() : void{
		$this->getLoader()->getDatabaseManager()->getConnection()->executeInsert(Queries::CREATE_PLAYER, ["username" => $this->getPlayer()->getLowerCaseName(), "created_at" => time()]);
	}

	public function initalizePlayerFromData() : void{
		$this->getLoader()->getDatabaseManager()->getConnection()->executeSelect(Queries::GET_PLAYER_BY_USERNAME, ["name" => $this->getPlayer()->getLowerCaseName()], function(array $rows) : void{
			var_dump($rows);
		});
	}

	public function canSwitchJobs() : bool{

		return true;
	}

	public function getRank() : int{
		return $this->rank;
	}

	public function getJob() : int{
		return $this->job;
	}

	public function setJob(int $job) : void{
		$this->job = $job;
	}
}