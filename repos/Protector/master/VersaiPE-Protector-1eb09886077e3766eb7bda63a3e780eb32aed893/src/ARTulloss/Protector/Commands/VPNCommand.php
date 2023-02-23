<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 3/30/2019
 * Time: 8:37 PM
 */
declare(strict_types=1);

namespace ARTulloss\Protector\Commands;

use ARTulloss\Protector\Constants\Constants;
use ARTulloss\Protector\Protector;
use ARTulloss\Protector\Task\VPNCheck;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\Plugin;
use function str_replace;

class VPNCommand extends PluginCommand
{
    /**
     * VPNCommand constructor.
     * @param string $name
     * @param Plugin $owner
     */
    public function __construct(string $name, Plugin $owner)
    {
        parent::__construct($name, $owner);
        $this->setDescription(Constants::VPN_COMMAND_DESCRIPTION);
        $this->setUsage(Constants::VPN_COMMAND_USAGE);
        $this->setPermission(Constants::VPN_COMMAND_PERMISSION);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$this->testPermission($sender)) {
            return;
        }
        
        if(isset($args[0])) {
            /** @var Protector $plugin */
            $plugin = $this->getPlugin();
            switch ($args[0]) {
                case "reset":
                    $plugin->vpnData = [];
                    $sender->sendMessage(Constants::RESET_IPS);
                    $plugin->saveFiles();
                    return;
                case 'allow':
                    if(isset($args[1])) {
                        if(filter_var($args[1], FILTER_VALIDATE_IP) !== false) {
                            $plugin->vpnData[$args[1]] = VPNCheck::RESIDENTIAL;
                            $plugin->saveFiles();
                            $sender->sendMessage(str_replace('{ip}', $args[1], Constants::ALLOW_IP));
                        } else
                            $sender->sendMessage(Constants::NOT_AN_IP);
                    } else
                        $sender->sendMessage('Usage: /vpn allow <ip>');
                    return;
                case 'block':
                    if(isset($args[1])) {
                        if(filter_var($args[1], FILTER_VALIDATE_IP) !== false) {
                            $plugin->vpnData[$args[1]] = VPNCheck::HOSTING;
                            $plugin->saveFiles();
                            $sender->sendMessage(str_replace('{ip}', $args[1], Constants::BLOCK_IP));
                        } else
                            $sender->sendMessage(Constants::NOT_AN_IP);
                    } else
                        $sender->sendMessage('Usage: /vpn block <ip>');
                    return;
            }
        }
        throw new InvalidCommandSyntaxException();
    }
}