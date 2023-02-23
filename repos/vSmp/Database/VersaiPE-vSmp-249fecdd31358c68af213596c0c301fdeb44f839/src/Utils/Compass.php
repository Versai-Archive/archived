<?php

declare(strict_types = 1);

/**
 * This file is for getting compass-like direction for players
 * 
 * @author Versai
 * @version 0.0.1
 */

namespace Versai\RPGCore\Utils;

class Compass {

    const N = 'N';
    const NE = 'NE';
    const E = 'E';
    const SE = 'SE';
    const S = 'S';
    const SW = 'SW';
    const W = 'W';
    const NW = 'NW';
    const DIAGONAL = [
		self::NE => "/",
		self::SE => "/",
		self::SW => "\\",
		self::NW => "\\"
	];
    const FULL = [
        self::N => "North",
		self::S => "South",
		self::E => "East",
		self::W => "West",
        self::NE => "North East",
		self::SE => "South East",
		self::SW => "South West",
		self::NW => "North West"
    ];
	
	/**
	* Gets the direction that the player is facing
	*
	* @param int $degrees
	**/
    public static function getCompassPointForDirection(float $degrees) {
        $degrees = ($degrees - 180) % 360;
        if ($degrees < 0)
            $degrees += 360;
        if (0 <= $degrees && $degrees < 22.5)
            return self::N;
        elseif (22.5 <= $degrees && $degrees < 67.5)
            return self::NE;
        elseif (67.5 <= $degrees && $degrees < 112.5)
            return self::E;
        elseif (112.5 <= $degrees && $degrees < 157.5)
            return self::SE;
        elseif (157.5 <= $degrees && $degrees < 202.5)
            return self::S;
        elseif (202.5 <= $degrees && $degrees < 247.5)
            return self::SW;
        elseif (247.5 <= $degrees && $degrees < 292.5)
            return self::W;
        elseif (292.5 <= $degrees && $degrees < 337.5)
            return self::NW;
        elseif (337.5 <= $degrees && $degrees < 360.0)
            return self::N;
        else
            return null;
    }
	
	/**
	* Gets the direction that the player is facing in emoji
	*
	* @param int $degrees
	**/
    public static function getCompassEmoji(float $degrees) {
        $degrees = ($degrees - 180) % 360;
        if ($degrees < 0)
            $degrees += 360;
        if (0 <= $degrees && $degrees < 22.5)
            return "";
        elseif (22.5 <= $degrees && $degrees < 67.5)
            return "";
        elseif (67.5 <= $degrees && $degrees < 112.5)
            return "";
        elseif (112.5 <= $degrees && $degrees < 157.5)
            return "";
        elseif (157.5 <= $degrees && $degrees < 202.5)
            return "";
        elseif (202.5 <= $degrees && $degrees < 247.5)
            return "";
        elseif (247.5 <= $degrees && $degrees < 292.5)
            return "";
        elseif (292.5 <= $degrees && $degrees < 337.5)
            return "";
        elseif (337.5 <= $degrees && $degrees < 360.0)
            return "";
        else
            return null;
    }
	
	/**
	* Gets the full direction that the player is facing
	*
	* @param float $degrees
	**/
    public static function getFullDirection(float $degrees) : string {
        $point = self::getCompassPointForDirection($degrees);
        return $point ? self::FULL[$point] : "";
    }
}