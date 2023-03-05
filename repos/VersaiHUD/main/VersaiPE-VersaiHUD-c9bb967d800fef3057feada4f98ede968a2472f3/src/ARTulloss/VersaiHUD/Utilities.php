<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/15/2020
 * Time: 7:18 PM
 */
declare(strict_types=1);

namespace ARTulloss\VersaiHUD;

class Utilities{
    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    static function gcd(int $a, int $b) {
        if ($a === 0)
            return $b;
        return self::gcd($b % $a, $a);
    }
    /**
     * @param int $a
     * @param int $b
     * @return float|int
     */
    static function lcm(int $a, int $b) {
        return ($a * $b) / self::gcd($a, $b);
    }

}