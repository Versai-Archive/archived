<?php
namespace Versai\ModerationPM\Commands\Form\Punishments;

use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;
use Versai\ModerationPM\Discord\Colors;

interface PunishmentCommand{

    public const TYPE = Punishment::TYPE_BAN;
    public const COLOR = Colors::RED;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Success!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was {action} by {staff}';
}
