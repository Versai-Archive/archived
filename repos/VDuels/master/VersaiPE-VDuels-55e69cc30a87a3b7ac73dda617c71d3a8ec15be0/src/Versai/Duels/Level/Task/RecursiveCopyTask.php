<?php
declare(strict_types=1);

namespace Versai\Duels\Level\Task;

use Versai\Duels\Level\LevelManager;

class RecursiveCopyTask extends CallableAsyncTask {

	/** @var string $src */
	protected string $src;
	/** @var string $dst */
	protected string $dst;

	/**
	 * RecursiveCopyTask constructor.
	 * @param string $src
	 * @param string $dst
     * @param null|callable $callable
	 */
	public function __construct(string $src, string $dst, ?callable $callable){
	    parent::__construct($callable);
		$this->src = $src;
		$this->dst = $dst;
	}

	public function onRun(): void{
		LevelManager::recurseCopy($this->src, $this->dst);
	}

}