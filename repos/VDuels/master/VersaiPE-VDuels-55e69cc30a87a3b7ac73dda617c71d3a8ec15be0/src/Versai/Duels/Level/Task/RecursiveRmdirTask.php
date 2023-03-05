<?php
declare(strict_types=1);

namespace Versai\Duels\Level\Task;

use pocketmine\Server;
use Versai\Duels\Level\LevelManager;

class RecursiveRmdirTask extends CallableAsyncTask {

	/** @var string $src */
	private string $src;

    /**
     * RecursiveRmdirTask constructor.
     * @param string $src
     * @param null|callable $callable
     */
	public function __construct(string $src, ?callable $callable){
	    parent::__construct($callable);
		$this->src = $src;
	}

    public function onRun(): void {
        LevelManager::rmdirRecursive($this->src);
    }

    public function onCompletion(): void {
        $server = Server::getInstance();
        $server->getLogger()->debug("World {$this->src} was removed");
    }
}