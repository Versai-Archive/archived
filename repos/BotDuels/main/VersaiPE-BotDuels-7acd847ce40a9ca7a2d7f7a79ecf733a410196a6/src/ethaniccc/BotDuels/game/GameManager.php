<?php

namespace ethaniccc\BotDuels\game;

use ethaniccc\BotDuels\Utils;

final class GameManager {

	private static $instance;
	/** @var DuelGame[] */
	public $games = [];
	/** @var string[] */
	public $inGame = [];

	public static function init(): void {
		self::$instance = new self();
	}

	public static function getInstance(): self {
		return self::$instance;
	}

	public function tick(): void {
		Utils::iterate($this->games, function ($key, DuelGame $game): void {
			$game->tick();
		});
	}

	public function add(DuelGame $game): void {
		$this->inGame[$game->player->getName()] = 0;
		$this->games[spl_object_hash($game)] = $game;
	}

	public function remove(DuelGame $game): void {
		unset($this->inGame[$game->player->getName()]);
		unset($this->games[spl_object_hash($game)]);
	}

	public function isInGame(string $name): bool {
		return isset($this->inGame[$name]);
	}

	public function getGame(string $name): ?DuelGame {
		foreach ($this->games as $duelGame) {
			if ($duelGame->player !== null && !$duelGame->player->isClosed() && $duelGame->player->getName() === $name) {
				return $duelGame;
			}
		}
		return null;
	}

}