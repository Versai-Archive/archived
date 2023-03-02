<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Versai\ModerationPM\Commands\Arguments\PlayerArgument;
use Versai\ModerationPM\Database\Container\PlayerData;
use Versai\ModerationPM\Database\Provider;
use Versai\ModerationPM\Main;

abstract class ModerationCommand extends BaseCommand implements CommandConstants{

    protected const TITLE = 'Moderation';

    protected Provider $provider;
    protected Main $plugin;

    /**
     * ModerationCommand constructor.
     * @param Main $main
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(Main $main, string $name, string $description = "", array $aliases = []){
        parent::__construct($main, $name, $description, $aliases);
        $this->plugin = $main;
        $this->provider = $main->getProvider();
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void{
        $this->registerArgument(0, new PlayerArgument('player'));
    }

    /**
     * @param CommandSender $sender
     * @param string $name
     * @param bool $silent
     * @return Player|null
     */
    public function resolveOnlinePlayer(CommandSender $sender, string $name, bool $silent = false): ?Player{
        $player = $sender->getServer()->getPlayerByPrefix($name);
        if ($player === null && !$silent){
            $sender->sendMessage(self::PLAYER_OFFLINE);
        }
        return $player;
    }

    /**
     * @param string $playerName
     * @param string|null $xuid
     * @param string|null $device_id
     * @param bool $inclusive
     * @param callable $callback
     */
    public function passPlayerData(string $playerName, ?string $xuid, ?string $device_id, bool $inclusive, callable $callback): void{
        $this->provider->asyncGetPlayer($playerName, $xuid, $device_id, $inclusive, function (array $result) use ($callback): void {
            foreach ($result as $player){
                $data = PlayerData::fromDatabaseQuery($player, PlayerData::NO_KEY);
                if ($data !== null){
                    $dataArray[] = $data;
                }
            }
            $callback($dataArray ?? null);
        });
    }
}
