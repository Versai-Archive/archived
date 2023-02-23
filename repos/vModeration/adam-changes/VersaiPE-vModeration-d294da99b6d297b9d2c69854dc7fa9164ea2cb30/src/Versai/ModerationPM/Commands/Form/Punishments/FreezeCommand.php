<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Form\Punishments;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;
use Versai\ModerationPM\Discord\Colors;

class FreezeCommand extends FormPunishmentModerationCommand{

    protected const TITLE = 'Freeze {player}';
    public const TYPE = Punishment::TYPE_FREEZE;
    public const COLOR = Colors::BLUE;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully froze {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was frozen by {staff}';

    public function onlinePunish(Player $player, string $message): void{
        $player->sendMessage($message);
        $this->plugin->getFrozen()->action($player);
        $player->setImmobile();
    }
}
