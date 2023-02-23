<?php
declare(strict_types=1);

namespace Versai\V6\database;

use Versai\V6\Loader;

abstract class Provider {

    public function __construct(protected Loader $loader, callable $initialize) {
        $this->initialize($initialize);
    }

    /**
     * @param callable $onComplete
     */
    abstract public function initialize(callable $onComplete) : void;
}