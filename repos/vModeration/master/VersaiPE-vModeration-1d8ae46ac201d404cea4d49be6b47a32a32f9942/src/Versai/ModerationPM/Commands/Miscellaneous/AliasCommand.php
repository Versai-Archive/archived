<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Miscellaneous;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Commands\ModerationCommand;
use Versai\ModerationPM\Database\Container\PlayerData;
use Versai\ModerationPM\Main;
use Versai\ModerationPM\Utilities\Utilities;
use function str_replace;
use function strtolower;

class AliasCommand extends ModerationCommand{

    public const MESSAGE_INITIAL = 'Alias for {player}';
    public const MESSAGE_MATCHES = '{player} with ' . TextFormat::RED . '{matches}' . TextFormat::WHITE . ' matches';
    private $aliases = [];

    public function __construct(Main $main, string $name, string $description = "", array $aliases = []){
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . 'alias');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if (!$this->testPermission($sender)){
            return;
        }
        if (!isset($args['player'])){
            throw new InvalidCommandSyntaxException();
        }

        $player = $this->plugin->getServer()->getPlayerByPrefix($args['player']);
        $xuid = null;
        $ip = null;
        $deviceID = null;
        if ($player !== null){
            $args['player'] = $player->getName();
            $xuid = $player->getXuid();
            $deviceID = $this->plugin->getListener()->getDeviceIDs()[$args['player']];
            $config = $this->plugin->getConfig();
            $ip = Utilities::hash($player->getNetworkSession()->getIp(), $config->getNested('Hash.Beginning'), $config->getNested('Hash.End'));
        }
        $this->passPlayerDataIP($args['player'], $xuid, $deviceID, $ip, true, function (?array $playerDataArray) use ($sender, $args): void{
            if ($playerDataArray !== null){
                $lastValue = end($playerDataArray);
                /** @var PlayerData $playerData */
                foreach ($playerDataArray as $playerData){
                    $final = false;
                    if ($playerData === $lastValue){
                        $final = true;
                    }
                    $this->passPlayerDataIP($playerData->getName(), $playerData->getXUID(), $playerData->getDeviceID(), $playerData->getHashedIP(), true, function (?array $playerDataArray) use ($sender, $args, $final): void{
                        if ($playerDataArray !== null) {
                            /** @var PlayerData $playerData */
                            foreach ($playerDataArray as $playerData){
                                $name = $playerData->getName();
                                if (!isset($this->aliases[$name])){
                                    $this->aliases[$name] = 1;
                                }else{
                                    $this->aliases[$name]++;
                                }
                            }
                        }
                        if ($final){
                            $sender->sendMessage(str_replace('{player}', $args['player'], self::MESSAGE_INITIAL));
                            foreach ($this->aliases as $name => $matches){
                                if (strtolower($name) !== strtolower($args['player']))
                                    $sender->sendMessage(str_replace(['{player}', '{matches}'], [$name, $matches], self::MESSAGE_MATCHES));
                            }
                            $this->aliases = [];
                        }
                    });
                }
            }
        });
    }

    /**
     * @param string $playerName
     * @param string|null $xuid
     * @param string|null $device_id
     * @param string|null $ip
     * @param bool $inclusive
     * @param callable $callback
     */
    public function passPlayerDataIP(string $playerName, ?string $xuid, ?string $device_id, ?string $ip, bool $inclusive, callable $callback): void
    {
        $this->provider->asyncGetPlayerIP($playerName, $xuid, $device_id, $ip, $inclusive, function (array $result) use ($callback): void {
            foreach ($result as $player) {
                $data = PlayerData::fromDatabaseQuery($player, PlayerData::NO_KEY);
                if ($data !== null)
                    $dataArray[] = $data;
            }
            $callback($dataArray ?? null);
        });
    }
}
