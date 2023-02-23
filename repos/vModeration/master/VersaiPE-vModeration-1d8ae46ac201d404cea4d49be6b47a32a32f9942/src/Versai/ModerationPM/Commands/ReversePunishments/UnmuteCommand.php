<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\ReversePunishments;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;

class UnmuteCommand extends ReversePunishmentCommand{

    protected const TYPE = Punishment::TYPE_MUTE;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully unmuted {player}!';
    protected const MESSAGE_SUCCESS_ONLINE = TextFormat::GREEN . 'You were unmuted!';
    protected const MESSAGE_FAIL = TextFormat::RED . 'Player was not muted!';
    protected const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was unmuted by {staff}';

    public function onlineUnpunish(Player $player, string $message): void{
        $this->plugin->getMuted()->reverseAction($player);
        $player->sendMessage($message);
    }
}
