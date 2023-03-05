<?php
declare(strict_types=1);

namespace Versai\Duels\Level\Task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;

abstract class CallableAsyncTask extends AsyncTask
{

    /** @var bool $safe */
    private bool $safe;

    /**
     * CallableAsyncTask constructor.
     * @param callable|null $callable
     */
    public function __construct(callable $callable = null)
    {
        if($callable !== null) {
            $this->setCallable($callable);
        }
    }

    /**
     * @param callable $callable
     */
    public function setCallable(callable $callable): void{
        Utils::validateCallableSignature(function (): void {}, $callable);
        $this->storeLocal("callable", $callable);
        $this->safe = true;
    }

    public function onCompletion(): void{
        $local = $this->fetchLocal("callable");
        call_user_func($local);
    }
}