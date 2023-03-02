<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Form\Punishments\Traits;

trait ReasonsTrait{

    /** @var string[] $reasons */
    protected array $reasons;

    /**
     * @param string[] $reasons
     */
    protected function setReasons(array $reasons): void{
        $this->reasons = $reasons;
    }
}
