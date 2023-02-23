<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/11/2020
 * Time: 2:48 PM
 */
declare(strict_types=1);

namespace ARTulloss\VersaiSettings;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class SettingsCommand extends PluginCommand{
    /**
     * SettingsCommand constructor.
     * @param string $name
     * @param Plugin $owner
     */
    public function __construct(string $name, Plugin $owner) {
        parent::__construct($name, $owner);
        $this->setDescription('Server settings!');
    }
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if($sender instanceof Player) {
            /** @var Main $plugin */
            $plugin = $this->getPlugin();
            $sender->sendForm($plugin->getForm($plugin->getPlayerSettings($sender)));
        } else
            $sender->sendMessage(TextFormat::RED . 'You have to be a player to execute this command!');
    }
}