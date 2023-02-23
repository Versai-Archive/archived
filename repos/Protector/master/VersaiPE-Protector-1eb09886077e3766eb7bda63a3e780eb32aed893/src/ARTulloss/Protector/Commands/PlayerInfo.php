<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/4/2019
 * Time: 6:14 PM
 */
declare(strict_types=1);
namespace ARTulloss\Protector\Commands;

use ARTulloss\Groups\Groups;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;

use ARTulloss\Formify\FormifyPlayer;
use ARTulloss\Protector\Constants\Constants;
use ARTulloss\Protector\Protector;

/**
 * Class PlayerInfo
 * @package ARTulloss\Protector\Commands
 */
class PlayerInfo extends PluginCommand
{
    /**
     * PlayerInfo constructor.
     * @param string $name
     * @param Protector $protector
     */
	public function __construct(string $name, Protector $protector)
	{
		parent::__construct($name, $protector);
		$this->setDescription(Constants::PLAYER_INFO_DESCRIPTION);
		$this->setUsage(Constants::PLAYER_INFO_USAGE);
		$this->setPermission(Constants::PLAYER_INFO_PERMISSION);
	}

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed|void
     * @throws \Exception
     */
	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if($this->testPermission($sender)) {

            if (isset($args[0])) {

                /** @var Protector $protector */
                $protector = $this->getPlugin();

                $data = Groups::getInstance()->playerHandler->getPlayerData($args[0]);

                if ($data === null) {
                    $player = $sender->getServer()->getPlayer($args[0]);
                    if($player === null) {
                        $sender->sendMessage(Constants::PLAYER_NOT_EXIST);
                        return;
                    } else
                        $name = $player->getName();
                } else {
                    $name = $data->getUsername();
                    $player = $protector->getServer()->getPlayerExact($name);
                }

                $sender->sendMessage(TextFormat::BLUE . "Information for $name");


                    $ip = $sender->isOp() ? $player->getAddress() : '0.0.0.0';

                $onlineMessage = [
                    "Online Data - ",
                    'UUID: ' . $player->getUniqueId(),
                    'IP: ' . $ip,
                    'OS: ' . $protector->translateDeviceOS($protector->getDeviceOS()->getDeviceOS($player) ?? 1),
                    'Locale: ' . $player->getLocale(),
                    'Ping: ' . $player->getPing(),
                ];

                foreach ($onlineMessage as $line)
                    $sender->sendMessage(TextFormat::BLUE . $line);

                $group_expires_string = 'Never';

                if ($data !== null) {
                    $group = $data->getGroup();
                    $group_expires_time = $data->getGroupExpires();
                    if ($group_expires_time !== null) {
                        $group_expires_string = $group_expires_time->diff(new \DateTime())->format('%d days, %h hours %i minutes and %s seconds');
                    }
                } else
                    return;

                $uuids = '';

                foreach ((array)$protector->multi_array_search($name, $protector->cids) as $uuid)
                    $uuids .= ', ' . $uuid;

                $uuids = substr($uuids, 2);

                $devices = '';

                foreach ((array)$protector->multi_array_search($name, $protector->devices) as $device)
                    $devices .= ', ' . $device;

                $devices = substr($devices, 2);

                $offlineMessage =
                    [
                        "Offline Data - ",
                        "Group: $group",
                        "Group Expires: $group_expires_string",
                        'UUIDs: ' . $uuids,
                        'Devices: ' . $devices,
                    ];

                if ($sender->isOp()) {

                    $ips = '';

                    foreach ((array)$protector->multi_array_search($name, $protector->ips) as $ip)
                        $ips .= ', ' . $ip;

                    $ips = substr($ips, 2);

                    $offlineMessage = array_unique(array_merge($offlineMessage, ['IPs: ' . $ips]));

                }

                foreach ($offlineMessage as $line)
                    $sender->sendMessage(TextFormat::BLUE . $line);

            } else
                throw new InvalidCommandSyntaxException();

        }
	}
}