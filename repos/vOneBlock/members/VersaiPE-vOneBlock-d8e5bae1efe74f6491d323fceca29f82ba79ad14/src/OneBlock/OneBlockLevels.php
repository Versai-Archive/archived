<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\OneBlock;

class OneBlockLevels {

	public static function getLevelName(int $level): string {
		return match ($level) {
			0 => "§2Plains",
			1 => "§bWinter",
			2 => "§3Ocean",
			3 => "§aJungle",
			4 => "§5Swamp",
			5 => "§7Dung§8eon",
			6 => "§gDesert",
			default => "Not Found!"
		};
	}

}