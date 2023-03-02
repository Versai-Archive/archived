<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\ReversePunishments;

use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;

class UnbanCommand extends ReversePunishmentCommand{

    protected const TYPE = Punishment::TYPE_BAN;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully unbanned {player}!';
    protected const MESSAGE_FAIL = TextFormat::RED . 'Player was not banned!';
    protected const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was unbanned by {staff}';
}
