<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\ReversePunishments;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use Versai\ModerationPM\Commands\Arguments\SilentArgument;
use Versai\ModerationPM\Commands\ModerationCommand;
use Versai\ModerationPM\Database\Container\PlayerData;
use Versai\ModerationPM\Discord\Colors;
use Versai\ModerationPM\Main;
use function str_replace;
use function strtolower;

abstract class ReversePunishmentCommand extends ModerationCommand{

    protected const TYPE = 0;
    protected const MESSAGE_SUCCESS = 'Success';
    protected const MESSAGE_SUCCESS_ONLINE = 'Success';
    protected const MESSAGE_FAIL = 'Fail';
    protected const MESSAGE_BROADCAST = '{player} was {action} by {staff}';
    protected const COLOR = Colors::GREEN;

    /** @var string[] $names */
    private array $names;

    protected function prepare(): void{
        parent::prepare();
        $this->registerArgument(1, new SilentArgument('silent', true));
    }

    public function __construct(Main $main, string $name, string $description = "", array $aliases = []){
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . 'un' . $this->provider->typeToString(static::TYPE, false));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if ($this->testPermission($sender)) {
            if(!isset($args['player'])){
                throw new InvalidCommandSyntaxException();
            }
            $name = $args['player'];
            if(isset($args['silent'])){
                $silent = in_array($args['silent'], ['-s', '-silent', 'true']);
            } else {
                $silent = false;
            }
            $this->passPlayerData($name, null, null, true, function (?array $dataArray) use ($silent, $sender, $name): void {
                if ($dataArray === null)
                    return;
                $this->names = [];
                $lastPlayerData = end($dataArray);
                /** @var PlayerData $playerData */
                foreach ($dataArray as $playerData) {
                    $xuid = $playerData->getXUID();
                    $device_id = $playerData->getDeviceID();
                    $names[$playerData->getName()] = true;
                    $this->passPlayerData($name, $xuid, $device_id, true, function (?array $dataArray) use ($silent, $sender, $name, $playerData, $lastPlayerData): void {
                        if ($dataArray !== null) {
                            $lowerCaseName = strtolower($name);
                            $lastData = end($dataArray);
                            /** @var PlayerData $data */
                            foreach ($dataArray as $data) {
                                $name2 = $data->getName();
                                $id = $data->getID();
                                $this->names[$data->getName()] = true;
                                $this->provider->asyncRemovePunishment($id, static::TYPE, function (int $rows) use ($sender, $lowerCaseName, $name2, $name): void {
                                    $player = $sender->getServer()->getPlayerByPrefix($name2);
                                    if ($player !== null)
                                        $this->onlineUnpunish($player, str_replace('{player}', $player->getName(), static::MESSAGE_SUCCESS_ONLINE));
                                    if ($rows === 0) {
                                        if($name2 === $name) { //Hack to stop message spam
                                            $sender->sendMessage(str_replace('{player}', $name2, static::MESSAGE_FAIL));
                                        }
                                        return;
                                    }
                                    if($name2 === $name) { //Hack to stop message spam
                                        $sender->sendMessage(str_replace('{player}', $name2, static::MESSAGE_SUCCESS));
                                    }
                                });
                                if (!$silent && $playerData === $lastPlayerData && $data === $lastData) {
                                    /*foreach ($this->names as $name => $true) {
                                        $sender->getServer()->broadcastMessage(str_replace(['{player}', '{staff}'], [$name, $sender->getName()], static::MESSAGE_BROADCAST));
                                    }*/
                                    $content = $this->plugin->getCommandConfig()->getAll()['Discord']['Content-Unpunish'];
                                    $logger = $this->plugin->getDiscordLogger();
                                    if($logger !== null) {
                                        foreach ($content as $key => $line) {
                                            $content[$key] = str_replace(['{player}', '{staff}'], [$logger->getXblLinkMarkdown($name), $logger->getXblLinkMarkdown($sender->getName())], $line);
                                        }
                                        $logger->logGeneric('Un' . $this->provider->typeToString(static::TYPE, false), $content, static::COLOR);
                                    }
                                    $sender->getServer()->broadcastMessage(str_replace(['{player}', '{staff}'], [$name, $sender->getName()], static::MESSAGE_BROADCAST));
                                }
                            }
                        }
                    });
                }
            });
        }
    }

    /**
     * @param Player $player
     * @param string $message
     */
    public function onlineUnpunish(Player $player, string $message): void
    {
    }
}
