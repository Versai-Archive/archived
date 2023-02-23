<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Miscellaneous;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\permission\DefaultPermissions;
use pocketmine\utils\TextFormat;
use CortexPE\Hierarchy\Hierarchy;
use Versai\ModerationPM\Commands\ModerationCommand;
use Versai\ModerationPM\Main;
use Versai\ModerationPM\Utilities\Utilities;

class PlayerInfoCommand extends ModerationCommand {

    public function __construct(Main $main, string $name, string $description = "", array $aliases = []) {
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . 'pinfo');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$this->testPermission($sender)){
            return;
        }
        if (!isset($args['player'])){
            throw new InvalidCommandSyntaxException();
        }
        $server = $this->plugin->getServer();
        $player = $server->getPlayerByPrefix($args['player']);

        if($player === null) {
            $sender->sendMessage(self::PLAYER_OFFLINE);
            return;
        } else {
            $name = $player->getName();
        }

        /* @var Hierarchy $groups */
        if(($groups = $server->getPluginManager()->getPlugin("vHierarchy")) !== null){
            $groupMember = $groups->getMemberFactory()->getMember($player);
            $role = $groupMember->getTopRole()->getName();
        }


        $sender->sendMessage(TextFormat::BLUE . "Information for $name");

        $ip = $sender->hasPermission(DefaultPermissions::ROOT_OPERATOR) ? $player->getNetworkSession()->getIp() : '0.0.0.0';
        $onlineMessage = [
            //'Rank: ' . $role,
            'UUID: ' . $player->getUniqueId(),
            'IP: ' . $ip,
            'OS: ' . Utilities::translateDeviceOS($this->plugin->getDeviceManager()->getDeviceOS($player) ?? 1),
            'Input Mode: ' . Utilities::translateInputMode($this->plugin->getDeviceManager()->getInputMode($player)?? 1),
            'Locale: ' . $player->getLocale(),
            'Ping: ' . $player->getNetworkSession()->getPing(),
        ];

        foreach ($onlineMessage as $line) {
            $sender->sendMessage(TextFormat::BLUE . $line);
        }
    }
}