<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Scoreboard;

class ScoreboardManager {

	/** @var Scoreboard[] */
	private static $scoreboards = [];

	public function __construct() {
		self::$scoreboards = [];
	}

	public static function addScoreboard(Scoreboard $scoreboard) {
		foreach(self::$scoreboards as $sb) {
			if ($scoreboard->id-1 === $sb->id) {
				return;
			}
		}
		self::$scoreboards[] = $scoreboard;
	}

	public static function removeScoreboard(Scoreboard $scoreboard) {
		unset(self::$scoreboards[array_search($scoreboard, self::$scoreboards)]);
	}

	public static function getScoreboards(): array {
		return self::$scoreboards;
	}

	public static function getScoreboard(int $id): ?Scoreboard {
		foreach(self::$scoreboards as $scoreboard) {
			if ($scoreboard->getId() === $id) {
				return $scoreboard;
			}
		}
		return null;
	}
}