<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Form\Punishments;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Database\Container\Punishment;
use Versai\ModerationPM\Discord\Colors;
use Exception;
use function str_replace;

class KickCommand extends FormNotStoredPunishmentModerationCommand{

    protected const TITLE = 'Kick {player}';
    public const TYPE = Punishment::TYPE_KICK;
    public const COLOR = Colors::YELLOW;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully kicked {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was kicked by {staff}';

    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param array $result
     * @throws Exception
     */
    protected function callback(CommandSender $sender, Player $player, array $result): void{
        $reason = $result['reason'];
        $this->logKick($sender, $player, $result['reason']);
        $sender->sendMessage(str_replace('{player}', $player->getName(), self::MESSAGE_SUCCESS));
        $sender->getServer()->broadcastMessage(str_replace(['{player}', '{staff}'], [$player->getName(), $sender->getName()], self::MESSAGE_BROADCAST));
        $player->kick($this->plugin->resolvePunishmentMessage(Punishment::TYPE_KICK, $reason, null, $sender->getName()), "");
    }
}
