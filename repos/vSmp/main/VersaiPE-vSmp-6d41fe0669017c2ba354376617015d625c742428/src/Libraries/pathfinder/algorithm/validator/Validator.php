<?php
/*
 * Copyright (c) Matze997
 * All rights reserved.
 * Under GPL license
 */

declare(strict_types=1);

namespace Versai\RPGCore\Libraries\pathfinder\algorithm\validator;

use Versai\RPGCore\Libraries\pathfinder\algorithm\Algorithm;
use pocketmine\math\Vector3;

abstract class Validator {
    abstract public function isSafeToStandAt(Algorithm $algorithm, Vector3 $vector3): bool;
}