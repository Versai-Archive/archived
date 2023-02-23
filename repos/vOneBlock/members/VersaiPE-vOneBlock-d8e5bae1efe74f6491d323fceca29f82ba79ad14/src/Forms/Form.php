<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types = 1);

namespace Versai\OneBlock\Forms;

use pocketmine\form\Form as F;
use pocketmine\player\Player;

abstract class Form implements F {

	protected $data = [];

	private $callable;

	public function __construct(?callable $callable) {
		$this->callable = $callable;
	}

	public function getCallable() : ?callable {
		return $this->callable;
	}

	public function setCallable(?callable $callable) {
		$this->callable = $callable;
	}

	public function handleResponse(Player $player, $data) : void {
		$this->processData($data);
		$callable = $this->getCallable();
		if($callable !== null) {
			$callable($player, $data);
		}
	}

	public function processData(&$data) : void {
	}

	public function jsonSerialize() {
		return $this->data;
	}
}