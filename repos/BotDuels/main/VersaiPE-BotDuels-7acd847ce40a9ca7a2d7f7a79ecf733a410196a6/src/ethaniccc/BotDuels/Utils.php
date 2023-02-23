<?php

namespace ethaniccc\BotDuels;

final class Utils {

	public static function iterate($iterator, callable $callable): void {
		foreach ($iterator as $key => $value) {
			$callable($key, $value);
		}
	}

	public static function vadilate($iterator, callable $callable, $error): void {
		if (!$callable($iterator))
			throw $error;
	}

	/**
	 * @param $src
	 * @param $dst
	 * @param string $childFolder
	 * @link https://stackoverflow.com/questions/2050859/copy-entire-contents-of-a-directory-to-another-using-php/2050965
	 * StackOverFlow to the rescue
	 */
	public static function recurseCopy($src, $dst, $childFolder = '') {
		$dir = opendir($src);
		@mkdir($dst);
		if ($childFolder != '') {
			@mkdir($dst . '/' . $childFolder);

			while (false !== ($file = readdir($dir))) {
				if (($file != '.') && ($file != '..')) {
					if (is_dir($src . '/' . $file)) {
						self::recurseCopy($src . '/' . $file, $dst . '/' . $childFolder . '/' . $file);
					} else {
						copy($src . '/' . $file, $dst . '/' . $childFolder . '/' . $file);
					}
				}
			}
		} else {
			// return $cc;
			while (false !== ($file = readdir($dir))) {
				if (($file != '.') && ($file != '..')) {
					if (is_dir($src . '/' . $file)) {
						self::recurseCopy($src . '/' . $file, $dst . '/' . $file);
					} else {
						copy($src . '/' . $file, $dst . '/' . $file);
					}
				}
			}
		}

		closedir($dir);
	}

	public static function rmdirRecursive(string $dir) {
		$files = array_diff(scandir($dir), ['.', '..']);
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? self::rmdirRecursive("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

}