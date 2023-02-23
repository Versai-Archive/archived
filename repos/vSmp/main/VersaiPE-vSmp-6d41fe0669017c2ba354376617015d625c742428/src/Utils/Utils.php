<?php

namespace Versai\RPGCore\Utils;

use pocketmine\item\Item;

class Utils {

    public static function map($value, $fromLow, $fromHigh, $toLow, $toHigh) {
        $fromRange = $fromHigh - $fromLow;
        $toRange = $toHigh - $toLow;
        $scaleFactor = $toRange / $fromRange;

        // Re-zero the value within the from range
        $tmpValue = $value - $fromLow;
        // Rescale the value to the to range
        $tmpValue *= $scaleFactor;
        // Re-zero back to the to range
        return $tmpValue + $toLow;
    }

    public static function isLevelUp(Item $item): bool {
        $nbt = $item->getNamedTag();
        $req = $nbt->getInt("req", 500);
        $xp = $nbt->getInt("xp", 0);
        $level = $nbt->getInt("level", 0);

        if ($xp == $req) {
            return true;
        } else {
            return false;
        }
    }
    
}