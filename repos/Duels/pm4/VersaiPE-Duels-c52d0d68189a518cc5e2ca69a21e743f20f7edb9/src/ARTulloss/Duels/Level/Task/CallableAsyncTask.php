<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2/26/2019
 * Time: 9:28 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Level\Task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;
use function call_user_func;

/**
 * Class LevelTask
 * @package ARTulloss\Duels\Level
 */
abstract class CallableAsyncTask extends AsyncTask
{
    /** @var bool $safe */
    private $safe;

	/**
	 * CallableAsyncTask constructor.
	 * @param callable|null $callable
	 */
	public function __construct(callable $callable = null)
	{
		if($callable !== null)
			$this->setCallable($callable);
	}

	/**
	 * @param callable $callable
	 */
	public function setCallable(callable $callable): void{
		Utils::validateCallableSignature(function (): void {}, $callable);
		//$this->storeLocal((string)$callable);
		$this->safe = true;
	}

	/**
	 * @param Server $server
	 */
	public function onCompletion(): void{
	    $local = $this->fetchLocal();
	    call_user_func($local);
	}
}