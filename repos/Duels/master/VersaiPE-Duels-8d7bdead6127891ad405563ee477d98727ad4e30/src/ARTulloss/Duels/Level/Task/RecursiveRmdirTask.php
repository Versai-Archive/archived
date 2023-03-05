<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Level\Task;

use InvalidArgumentException;

/**
 * Class RecursiveRmdirTask
 * @package ARTulloss\Duels\Level
 */
class RecursiveRmdirTask extends CallableAsyncTask
{
	/** @var string $src */
	private $src;

    /**
     * RecursiveRmdirTask constructor.
     * @param string $src
     * @param callable|null $callable
     */
	public function __construct(string $src, ?callable $callable = null)
	{
		parent::__construct($callable);
		$this->src = $src;
	}

	public function onRun()
	{
		self::recursive_rmdir($this->src);
	}

	/**
	 * @param string $src
	 */
	public static function recursive_rmdir(string $src)
	{
		if (!is_dir($src))
			throw new InvalidArgumentException("$src must be a directory");

		if (substr($src, strlen($src) - 1, 1) !== '/')
			$src .= '/';

		$files = glob($src . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file))
				self::recursive_rmdir($file);
			else
				unlink($file);
		}
		rmdir($src);
	}
}