<?php

namespace Versai\ModerationPM\Commands\Form\Punishments;

use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;
use Versai\ModerationPM\Discord\Colors;

class BanIPCommand extends BanCommand{

    protected const TITLE = 'IP Ban {player}';
    public const TYPE = Punishment::TYPE_IP_BAN;
    public const COLOR = Colors::RED;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully IP banned {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was IP banned by {staff}';
}
