<?php

/******************************************************************************
 * Copyright (c) 2022.                                                        *
 * I (Caleb Leeman) Do not wish for anyone to use, edit, or run this code without my permission.
 * I also do not want this code used in other projects that are not mine.     *
 ******************************************************************************/

declare(strict_types=1);

namespace Versai\OneBlock\Translator;

use Versai\OneBlock\Main;

class Translator {

	const TRANSLATION_FILE = "translations.yml";

	public static function translate(string $id, array $args = []): string {
		$config = yaml_parse_file(Main::getInstance()->getDataFolder() . "translations.yml");
		$vars = explode(".", $id);
		$base = $config[$vars[0]] ?? $id;
		array_shift($vars);
		foreach($vars as $var) {
			if (!isset($base[$var])) {
				Main::getInstance()->getLogger()->warning("Translation " . $id . " could not be found");
				return $id;
			}
			$base = $base[$var];
		}

		foreach($args as $arg => $val) {
			$base = str_replace('{%' . $arg . '}', (string)$val, $base);
		}

		return $base;
	}

}