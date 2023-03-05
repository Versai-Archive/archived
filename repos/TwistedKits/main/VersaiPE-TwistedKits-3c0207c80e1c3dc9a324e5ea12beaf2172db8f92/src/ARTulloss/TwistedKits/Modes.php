<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/22/2020
 * Time: 1:25 PM
 */
declare(strict_types=1);

namespace ARTulloss\TwistedKits;

interface Modes{
    public const MODE_CLEAR_INVENTORY = 1;
    public const MODE_ADD_ITEMS = 2;
    public const MODE_ADD_ITEMS_CLEAR_EFFECTS = 3;
}