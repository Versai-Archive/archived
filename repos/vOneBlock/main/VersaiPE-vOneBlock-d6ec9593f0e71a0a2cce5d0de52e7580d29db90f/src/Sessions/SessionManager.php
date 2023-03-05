<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Sessions;

use pocketmine\player\Player;
use Versai\OneBlock\Main;
use function spl_object_hash;

class SessionManager {

	/** @var PlayerSession[] */
	private array $sessions = [];
	private Main $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function registerSession(Player $player){
		$this->sessions[spl_object_hash($player)] = new PlayerSession($player);
	}

	public function unregisterSession(Player $player){
		unset($this->sessions[spl_object_hash($player)]);
	}

	public function getSession(Player $player): ?PlayerSession{
		return $this->sessions[spl_object_hash($player)] ?? null;
	}

	public function getSessions(): array{
		return $this->sessions;
	}
}