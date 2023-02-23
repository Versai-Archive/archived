<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\ReversePunishments;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;

class UnfreezeCommand extends ReversePunishmentCommand{

    protected const TYPE = Punishment::TYPE_FREEZE;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully thawed {player}!';
    protected const MESSAGE_SUCCESS_ONLINE = TextFormat::GREEN . 'You were thawed!';
    protected const MESSAGE_FAIL = TextFormat::RED . 'Player was not frozen!';
    protected const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was thawed by {staff}';

    public function onlineUnpunish(Player $player, string $message): void{
        $this->plugin->getFrozen()->reverseAction($player);
        $player->setImmobile(false);
        $player->sendMessage($message);
    }
}
