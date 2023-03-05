<?php
declare(strict_types=1);

namespace Versai\vTempRanks;

class Utilities {

    public const DATE_TIME_REGEX = '/^(?:[0-9]+ )(?:seconds?|minutes?|hours?|days?|weeks?|months?|years?)$/i';
    public const DATE_TIME_REGEX_FAILED = "{length} violates length parameters! Must be a valid date time string";

    public const FOREVER = 0;

    public static function hasRankExpired(int $until): bool{
        $remaining = $until - time();
        if ($until === self::FOREVER || $remaining > 0) return false;

        return true;
    }
}