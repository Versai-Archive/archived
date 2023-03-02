<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\ReversePunishments;

use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;

class UnBanIPCommand extends ReversePunishmentCommand{

    protected const TYPE = Punishment::TYPE_IP_BAN;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . "Successfully unbanned {player}'s IP!";
    protected const MESSAGE_FAIL = TextFormat::RED . "Player's IP was not banned!";
    protected const MESSAGE_BROADCAST = TextFormat::GREEN . "{player}'s IP was unbanned by {staff}";

}
