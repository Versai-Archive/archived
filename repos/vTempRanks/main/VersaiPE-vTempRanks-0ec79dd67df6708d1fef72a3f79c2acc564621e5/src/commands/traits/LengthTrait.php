<?php
declare(strict_types=1);

namespace Versai\vTempRanks\commands\traits;

use InvalidArgumentException;
use Versai\vTempRanks\Utilities;

trait LengthTrait{

    /** @var string[] $lengths */
    protected array $lengths;

    /**
     * @param string[] $lengths
     */
    protected function setLengths(array $lengths): void{
        foreach ($lengths as $length) {
            if (preg_match(Utilities::DATE_TIME_REGEX, $length) === 0 && strtolower($length) !== 'forever') {
                throw new InvalidArgumentException(str_replace('{length}', $length, Utilities::DATE_TIME_REGEX_FAILED));
            }
        }
        $this->lengths = $lengths;
    }
}
