<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands;

use pocketmine\utils\TextFormat;

interface CommandConstants{

    public const PLAYER_ONLY = TextFormat::RED . 'You must be a player to use this command';
    public const PLAYER_OFFLINE = TextFormat::RED . "That player is offline or doesn't exist!";

}
