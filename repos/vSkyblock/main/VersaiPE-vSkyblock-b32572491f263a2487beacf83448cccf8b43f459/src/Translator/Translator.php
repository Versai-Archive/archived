<?php
declare(strict_types=1);

namespace Skyblock\Translator;

use Skyblock\Main;

class Translator {

	// make a system that has a function that can pull nested data from a yml file
	// make a system that has a function that can pull nested data from a json file
	public static function translate(string $key, array $args = []) {
		$config = yaml_parse_file(Main::getInstance()->getDataFolder() . "translations.yml");
		$vars = explode(".", $key);
		$base = $config[$vars[0]] ?? $key;
		array_shift($vars);
		foreach($vars as $var) {
			if (!isset($base[$var])) {
				return $key;
			}
			$base = $base[$var];
		}

		foreach($args as $arg => $val) {
			$base = str_replace('{' . $arg . '}', $val, $base);
		}

		return $base;
	}
}