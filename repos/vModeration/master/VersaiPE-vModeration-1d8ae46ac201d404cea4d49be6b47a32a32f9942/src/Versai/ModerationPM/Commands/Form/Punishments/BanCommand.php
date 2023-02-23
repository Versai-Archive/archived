<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Form\Punishments;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;
use Versai\ModerationPM\Discord\Colors;

class BanCommand extends FormPunishmentModerationCommand{

    protected const TITLE = 'Ban {player}';
    public const TYPE = Punishment::TYPE_BAN;
    public const COLOR = Colors::RED;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully banned {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was banned by {staff}';

    public function onlinePunish(Player $player, string $message): void{
        $player->kick($message, "");
    }
}
