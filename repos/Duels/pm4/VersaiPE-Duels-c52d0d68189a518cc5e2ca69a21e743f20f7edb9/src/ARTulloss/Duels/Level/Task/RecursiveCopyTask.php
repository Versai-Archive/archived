<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/26/2019
 * Time: 9:31 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Level\Task;

/**
 * Class RecursiveCopyTask
 * @package ARTulloss\Duels\Level
 */
class RecursiveCopyTask extends CallableAsyncTask
{
	/** @var string $src */
	protected $src;
	/** @var string $dst */
	protected $dst;

	/**
	 * RecursiveCopyTask constructor.
	 * @param string $src
	 * @param string $dst
	 * @param callable|null $callable
	 */
	public function __construct(string $src, string $dst, callable $callable = null)
	{
		parent::__construct($callable);
		$this->src = $src;
		$this->dst = $dst;
	}

	public function onRun(): void
	{
		self::recurse_copy($this->src, $this->dst);
	}

	/**
	 * @param string $src
	 * @param string $dst
	 */
	public static function recurse_copy(string $src, string $dst): void
	{
		$dir = opendir($src);
		@mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if ($file !== '.' && $file !== '..') {
				if (is_dir($src . '/' . $file))
					self::recurse_copy($src . '/' . $file, $dst . '/' . $file);
				else
					copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
		closedir($dir);
	}
}