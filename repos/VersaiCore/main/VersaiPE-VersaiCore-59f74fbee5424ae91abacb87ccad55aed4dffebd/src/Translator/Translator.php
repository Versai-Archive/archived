<?php

declare(strict_types=1);

namespace Versai\Translator;

/**
 * Translator class will hold the functions to translate strings
 *
 * Please construct this class as if it was a manager
 */
class Translator {

	public string $file;

	/**
	 * @param string $file The file in which the translations are located, currently only yaml files
	 */
	public function __construct(string $file) {
		$this->file = $file;
	}

	/**
	 * @param string $key The key that represents the path to the string
	 * @param array $args replace {x} in the corresponding key
	 * @return string
	 *
	 * @example translate("test.example", ["World"]);
	 */
	public function translate(string $key, array $args = []): string {
		// TODO: Add availability to JSON, INI, and ?TXT
		$config = yaml_parse_file(Main::getInstance()->getDataFolder() . $this->file);
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