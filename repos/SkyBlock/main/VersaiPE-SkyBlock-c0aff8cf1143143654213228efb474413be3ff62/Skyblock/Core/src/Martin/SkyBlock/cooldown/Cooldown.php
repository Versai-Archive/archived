<?php


namespace Martin\SkyBlock\cooldown;


class Cooldown{
	private int $type;
	private int $time;

	public function __construct(int $type){
		$this->type = $type;
	}

	public function getType() : int{
		return $this->type;
	}

	public function getTime() : int{
		return $this->time;
	}

	public function setTime(int $time) : void{
		$this->time = $time;
	}
}